<?php

namespace App\Repositories\Patient;

use App\Appointment;
use App\Models\TridiuumPatient;
use App\Patient;
use App\PatientDocument;
use App\Jobs\Tridiuum\AttachTridiuumDocsJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Interface ApiRepositoryInterface
 * @package App\Repositories\Square
 */
class PatientDocumentRepository implements PatientDocumentRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getLastDischargeDate(Patient $patient): ?Carbon
    {
        $dates = [
            $patient->assessmentForms()->discharge()->max('created_at'),
            $patient->documents()->discharged()->max('created_at'),
            $patient->electronicDocuments()->discharge()->max('created_at'),
        ];
        foreach ($dates as &$date) {
            if ($date) {
                $date = Carbon::parse($date)->startOfDay()->timestamp;
            }
        }
        sort($dates);
        $maxDate = last($dates);

        return $maxDate ? Carbon::createFromTimestamp($maxDate) : null;
    }

    public function loadTridiuumInitialAssessment(array $data): array
    {
        $appointmentId = $data['appointment_id'];
        $patientId = $data['patient_id'];

        $appointment = Appointment::query()
            ->where('id', $appointmentId)
            ->firstOrFail();

        if (!$appointment->is_initial) {
            return [
                'message' => trans('notifications.appointment_is_not_initial'),
                'status' => 'error',
            ];
        }

        if (!TridiuumPatient::where('internal_id', $patientId)->exists()) {
            return [
                'message' => trans('notifications.patient_doesnt_exist_in_tridiuum'),
                'status' => 'error',
            ];
        }

        $initialAssessmentQuery = PatientDocument::query()
            ->select(['id', 'created_at'])
            ->where('original_document_name', 'like', '%' . 'Initial' . '%')
            ->where('patient_id', $patientId)
            ->where('created_at', '>', Carbon::createFromTimestamp($appointment->time)->toDateTimeString());

        if ((clone $initialAssessmentQuery)->exists()) {
            return $this->attachInitialAssessmentToAppointment($initialAssessmentQuery, $appointment);
        }

        // if the initial assessment was not found in our system, then parse it from Lucet
        \Bus::dispatchNow(new AttachTridiuumDocsJob($appointmentId));

        // after the job execution, it is necessary to check again whether the initial assessment has been added
        if ((clone $initialAssessmentQuery)->exists()) {
            return $this->attachInitialAssessmentToAppointment($initialAssessmentQuery, $appointment);
        }

        return [
            'message' => trans('notifications.patient_doesnt_have_initial_assessment_in_tridiuum'),
            'status' => 'warning',
        ];
    }

    private function attachInitialAssessmentToAppointment(Builder $initialAssessmentQuery, Appointment $appointment): array
    {
        $initialAssessment = $initialAssessmentQuery->first();

        $appointment->update([
            'initial_assessment_type' => PatientDocument::class,
            'initial_assessment_id' => $initialAssessment->id,
            'initial_assessment_created_at' => $initialAssessment->created_at,
        ]);

        return [
            'message' => trans('notifications.successfully_synced_initial_assessment_from_tridiuum'),
            'status' => 'success',
        ];
    }
}
