<?php

namespace App\Jobs\Tridiuum;

use App\Appointment;
use App\Helpers\TridiuumHelper;
use App\Jobs\Officeally\Retry\RetryDeleteUpcomingAppointments;
use App\Models\TridiuumPatient;
use App\Models\TridiuumPatientDocument;
use App\Models\TridiuumPatientDocumentType;
use App\Patient;
use App\PatientComment;
use App\PatientDocument;
use App\PatientDocumentType;
use App\PatientStatus;
use App\Provider;
use App\Option;
use App\Traits\LogDataTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GetPatients extends AbstractParser
{
    const PATIENT_LIMIT = 1000;

    const PATIENT_DOCUMENT_TYPES = [
        'Initial Evaluation' => 'Initial Assessment Tridiuum',
        'Discharge' => 'Discharge Summary Tridiuum',
    ];

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogDataTrait;

    /** @var boolean */
    protected $withDocuments;

    /** @var TridiuumHelper */
    protected $tridiuumHelper;

    /**
     * Create a new job instance.
     *
     * @param boolean $withDocuments
     *
     * @return void
     */
    public function __construct($withDocuments = true)
    {
        $this->withDocuments = $withDocuments;

        $this->onQueue('tridiuum-parser');

        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        if (!config('parser.tridiuum.enabled')) {
            return;
        }
        $this->tridiuumHelper = new TridiuumHelper();
        $processedPatients = 0;
        while (true) {
            $this->logAdditionalData('processedPatients: ' . $processedPatients);
            $tridiuumPatients = data_get($this->tridiuumHelper->getPatientsInfo($processedPatients, self::PATIENT_LIMIT), 'data', []);
            $this->logAdditionalData('Successfully got patients from tridiuum');

            if (!$tridiuumPatients) {
                $this->logAdditionalData('Stopped processing patients: there are no more patients');
                break;
            }
            foreach ($tridiuumPatients as $tridiuumPatient) {
                $this->logAdditionalData('Start processing patient, mrn=' . data_get($tridiuumPatient, 'mrn', ''));
                $patient = $this->processingPatient($tridiuumPatient);
                $this->logAdditionalData('Successfully processed patient, id=' . optional($patient)->id);
                if (!$patient) {
                    $this->logAdditionalData([
                        'message' => 'Patient not created',
                        'tridiuum_patient' => $tridiuumPatient,
                    ]);
                    continue;
                }
                if ($this->withDocuments && !empty($patient->internal_id) && !empty($patient->current_track)) {
                    $this->logAdditionalData('Start downloading documents');
                    $this->downloadDocuments($patient);
                    $this->logAdditionalData('Successfully downloading documents');
                }
            }

            if (count($tridiuumPatients) < self::PATIENT_LIMIT) {
                $this->logAdditionalData('Break cycle. Count of tridiuum patients: ' . count($tridiuumPatients));
                break;
            }

            $processedPatients += self::PATIENT_LIMIT;
        }
        $this->logAdditionalData('Start attaching tridiuum patients');
        $this->attachPatints();
        $this->logAdditionalData('Successfully attached tridiuum patients');
        dispatch(new SyncPatientEmail());
        $this->logAdditionalData('Dispatched SyncPatientEmail job');
    }

    /**
     * @param array $tridiuumPatient
     */
    public function processingPatient(array $tridiuumPatient)
    {
        $name = explode(',', strip_tags(data_get($tridiuumPatient, 'name', [])));
        $dob = data_get($tridiuumPatient, 'dob', []);
        $mrnData = data_get($tridiuumPatient, 'mrn', []);

        $this->tridiuumHelper = new TridiuumHelper();
        $patientData = $this->tridiuumHelper->getPatientInfo($mrnData, $withValues = true);

        $firstName = sanitize_name(trim($name[1]));
        $lastName = sanitize_name(trim($name[0]));
        if (!$firstName || !$lastName) {
            return null;
        }

        $data = [
            'external_id' => $tridiuumPatient['DT_RowData']['id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_initial' => sanitize_name($patientData['middle_initial']),
            'email' => $patientData['email'],
            'mrn' => $mrnData,
            'parsed_at' => Carbon::now(),
            'is_active' => $patientData['active'],

            // 'middle_initial' => sanitize_name($tridiuumPatient['DT_RowData']['middle_initial']),
            // 'email'          => !empty($tridiuumPatient['DT_RowData']['email'])
            //     ? $tridiuumPatient['DT_RowData']['email']
            //     : (!empty($tridiuumPatient['DT_RowData']['notification_email']) ? $tridiuumPatient['DT_RowData']['notification_email'] : null),
            // 'mrn'            => $tridiuumPatient['DT_RowData']['mrn'],
            // 'parsed_at'      => Carbon::now(),
            // 'is_active'      => $tridiuumPatient['DT_RowData']['active'],

        ];
        if (!empty($dob)) {
            $data['date_of_birth'] = Carbon::parse($dob);
        }
        $patient = TridiuumPatient::updateOrCreate(array_only($data, ['external_id']), $data);
        if (empty($patient->current_track) || !$patient->current_track_updated_at || !Carbon::parse($patient->current_track_updated_at)->isToday()) {
            $patient->update([
                'current_track' => $this->tridiuumHelper->getCurrentTrack($patient->external_id),
                'current_track_updated_at' => Carbon::now(),
            ]);
        }

        return $patient->refresh();
    }

    public function attachPatints()
    {
        TridiuumPatient::query()->whereNull('internal_id')->chunkById('1000', function (Collection $tridiuumPatients) {
            $tridiuumPatients->each(function (TridiuumPatient $tridiuumPatient) {
                $fullname = explode(' ', $tridiuumPatient->first_name);
                $tridiuumPatientFirstName = $fullname[0];
                $patient = Patient::query()
                    ->where('first_name', $tridiuumPatientFirstName)
                    ->where('last_name', $tridiuumPatient->last_name)
                    ->where('date_of_birth', $tridiuumPatient->date_of_birth)
                    ->first();
                if ($patient) {
                    $tridiuumPatient->update(['internal_id' => $patient->getKey()]);
                }
            });
        });
    }

    public function downloadDocuments(TridiuumPatient $patient)
    {
        $reports = array_merge(
            $this->tridiuumHelper->getAssessmentsDocuments($patient->current_track),
            $this->tridiuumHelper->getNotesDocuments($patient->external_id, $patient->current_track)
        );

        $dischargeIDs = PatientDocumentType::getFileTypeIDsLikeDischarge();
        $dischargeStatusId = PatientStatus::getDischargedId();

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
                    ['tridiuum_patient_id' => $patient->id, 'type_id' => $type->id, 'is_downloaded' => $result['status']]
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
                        'patient_id' => $patient->internal_id,
                        'original_document_name' => $patient->first_name . ' ' . $patient->last_name . ' - ' . $report['name'] . '.pdf',
                        'aws_document_name' => $result['filename'],
                        'visible' => 1,
                        'only_for_admin' => 0,
                        'document_type_id' => $patientDocumentType->id ?? null,
                        'is_tridiuum_document' => 1,
                        'created_at' => $report['created_at'],
                        'updated_at' => $report['created_at'],
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

    private function logAdditionalData($data, string $type = 'INFO'): void
    {
        if (!config('parser.tridiuum.get_patients.additional_logging_enabled')) {
            return;
        }

        $this->logData(config('parser.tridiuum.get_patients.log_file_storage_path'), $data, $type);
    }
}
