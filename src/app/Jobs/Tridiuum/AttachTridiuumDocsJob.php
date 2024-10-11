<?php

namespace App\Jobs\Tridiuum;

use App\Jobs\Officeally\Retry\RetryDeleteUpcomingAppointments;
use App\Models\TridiuumPatientDocumentType;
use App\Option;
use App\PatientComment;
use App\PatientStatus;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Appointment;
use App\Helpers\TridiuumHelper;
use App\Models\TridiuumPatient;
use App\Models\TridiuumPatientDocument;
use App\Patient;
use App\PatientDocument;
use App\PatientDocumentType;

class AttachTridiuumDocsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PATIENT_DOCUMENT_TYPES = [
        'Initial Evaluation' => 'Initial Assessment Tridiuum',
        'Discharge' => 'Discharge Summary Tridiuum'
    ];

    private $appointmentId;

    protected $tridiuumHelper;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($appointmentId)
    {
        $this->appointmentId = $appointmentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->tridiuumHelper = new TridiuumHelper();

        $patient = Patient::where('id', Appointment::find($this->appointmentId)->patients_id)->first();
        $tridiuumPatient = TridiuumPatient::where('internal_id', $patient->id)->first();

        $dischargeIDs = PatientDocumentType::getFileTypeIDsLikeDischarge();
        $dischargeStatusId = PatientStatus::getDischargedId();

        if (isset($tridiuumPatient->current_track) && ($tridiuumPatient->external_id)) {
            $reports = array_merge(
                $this->tridiuumHelper->getAssessmentsDocuments($tridiuumPatient->current_track),
                $this->tridiuumHelper->getNotesDocuments($tridiuumPatient->external_id, $tridiuumPatient->current_track)
            );

            foreach ($reports as $report) {
                if (empty($report['id'])) {
                    continue;
                }

                $isReportTypeNote = $report['type'] === TridiuumHelper::REPORT_TYPE_NOTE;

                $type = TridiuumPatientDocumentType::firstOrCreate(['name' => $report['name']]);
                $document = TridiuumPatientDocument::where('external_id', $report['id'])->first();

                if (!$document || !$document->is_downloaded) {
                    $provider = null;

                    if ($isReportTypeNote) {
                        $providerName = Provider::sanitizeTridiuumProviderName($report['logged_by']);
                        $provider = Provider::withTrashed()->search($providerName)->first();

                        if (!$provider) {
                            continue;
                        }
                    }

                    $result = $this->tridiuumHelper->downloadDocument($report['id']);

                    $document = TridiuumPatientDocument::updateOrCreate(
                        ['external_id' => $report['id']],
                        ['tridiuum_patient_id' => $tridiuumPatient->id, 'type_id' => $type->id, 'is_downloaded' => $result['status']]
                    );

                    if ($result['status']) {
                        $typeName = 'Other Tridiuum';

                        foreach (self::PATIENT_DOCUMENT_TYPES as $key => $value) {
                            if (stristr($report['name'], $key) !== false) {
                                $typeName = $value;
                                break;
                            }
                        }

                        $patientDocumentType = PatientDocumentType::where('type', $typeName)->first();
                        $patientDocument = PatientDocument::create([
                            'patient_id' => $tridiuumPatient->internal_id,
                            'original_document_name' => $tridiuumPatient->first_name . ' ' . $tridiuumPatient->last_name . ' - ' . $report['name'] . '.pdf',
                            'aws_document_name' => $result['filename'],
                            'visible' => 1,
                            'only_for_admin' => 0,
                            'document_type_id' => $patientDocumentType->id ?? null,
                            'is_tridiuum_document' => 1,
                            'created_at' => $report['created_at'],
                            'updated_at' => $report['created_at']
                        ]);

                        $document->update([
                            'internal_id' => $patientDocument->id,
                        ]);

                        if ($isReportTypeNote) {
                            $documentTypeID = $patientDocument->document_type_id;
                            $isDischargedDocument = in_array($documentTypeID, $dischargeIDs);
                            $patientModel = $patientDocument->patient;
                            $isPatientStatusDischarged = $patientModel->status_id === $dischargeStatusId;

                            if ($isDischargedDocument && !$isPatientStatusDischarged) {
                                $this->changePatientStatusToDischarged($patientModel, $report, $provider);
                            }
                        }
                    }
                }
            }
        }
    }

    private function changePatientStatusToDischarged($patient, $report, $provider)
    {
        $hasAppointments = $patient->appointments()
            ->where('time', '>=', Carbon::parse($report['created_at'])->addDay()->startOfDay()->timestamp)
            ->where('time', '<=', Carbon::today()->endOfDay()->timestamp)
            ->exists();

        if ($hasAppointments) {
            return;
        }

        $systemComments = [];

        $patient->appointments()
            ->where('time', '>=', Carbon::parse($report['created_at'])->addDay()->startOfDay()->timestamp)
            ->where('providers_id', $provider->id)
            ->each(function (Appointment $appointment) use (&$systemComments) {
                $time = Carbon::createFromTimestamp($appointment->time);
                $comment = trans('comments.appointment_deleted_from_office_ally', [
                    'apptdate' => $time->format('m/d/Y'),
                    'appttime' => $time->format('h:iA'),
                ]);
                $systemComments[] = [
                    'comment' => $comment,
                    'patient_id' => $appointment->patients_id,
                ];
                $appointment->delete();
            });

        PatientComment::bulkAddComments($systemComments, true);
        dispatch(new RetryDeleteUpcomingAppointments($patient->id, Option::OA_ACCOUNT_3, $provider->officeally_id));

        PatientStatus::changeStatusAutomatically($patient->id, 'to_discharged');
    }
}
