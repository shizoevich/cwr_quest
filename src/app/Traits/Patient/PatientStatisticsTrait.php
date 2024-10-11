<?php

namespace App\Traits\Patient;

use App\Appointment;
use App\AssessmentForm;
use App\Models\Patient\PatientElectronicDocument;
use App\Patient;
use App\PatientDocument;
use App\PatientDocumentType;
use App\PatientStatus;
use App\Provider;
use App\Status;
use App\Models\GoogleMeeting;
use App\Models\RingcentralCallLog;
use Carbon\Carbon;

trait PatientStatisticsTrait
{
    public function getPatientNoteCount(Patient $patient, bool $withCoeff = false)
    {
        $notesCount = $patient->patientNotes()->where('is_finalized', true)->count();
        $count = $notesCount;

        if ($withCoeff) {
            $coeff = Patient::getPnCoefficient($patient->id);
            $count += $coeff;
        }

        return $count;
    }

    private function getPatientDraftProgressNoteCount(Patient $patient)
    {
        return $patient->patientNotes()->where('is_finalized', false)->count();
    }

    public function getMissingProgressNoteCount($patient)
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();

        return Appointment::query()
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patients.id')
            ->leftJoin('patient_notes', function ($join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('appointments.note_on_paper', '=', 0)
            ->where('appointments.is_initial', '=', 0)
            ->whereNull('appointments.initial_assessment_id')
            ->where(function ($query) {
                $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
            })
            ->where('patients_has_providers.providers_id', '=', \DB::raw('appointments.providers_id'))
            ->where('patients_has_providers.chart_read_only', '=', false)
            ->where('appointments.patients_id', '=', $patient->id)
            ->count();
    }

    public function getPatientAppointmentVisitCreatedCount(Patient $patient)
    {
        $visit_created_id = Status::getVisitCreatedId();

        return $patient->appointments()
            ->where('appointment_statuses_id', $visit_created_id)
            ->count();
    }

    public function getPatientAppointmentCompletedCount(Patient $patient)
    {
        $completedId = Status::getCompletedId();

        return $patient->appointments()
            ->where('appointment_statuses_id', $completedId)
            ->count();
    }

    private function getPatientCancelledAppointments(Patient $patient)
    {
        $cancelStatusIds = Status::getNewCancelStatusesId();

        return $patient->appointments()
            ->with('status')
            ->whereIn('appointment_statuses_id', $cancelStatusIds)
            ->get();
    }

    private function getPatientInitialAssessmentCount(Patient $patient)
    {
        $initialAssessmentsPatientElectronicDocument = PatientElectronicDocument::select('id')
            ->where('patient_id', $patient->id)
            ->whereIn('document_type_id', AssessmentForm::getFileTypeIDsLikeInitialAssessment())
            ->count();

        $initialAssessmentsPatientDocument = PatientDocument::select('id')
            ->where('patient_id', $patient->id)
            ->where('visible', 1)
            ->whereIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeInitialAssessment())
            ->count();

        return $initialAssessmentsPatientElectronicDocument + $initialAssessmentsPatientDocument;
    }

    private function getPatientGoogleMeetingsAppointmentCount(int $patientId)
    {
        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();

        return GoogleMeeting::query()
            ->join('google_meeting_call_logs', 'google_meeting_call_logs.google_meeting_id', '=', 'google_meetings.id')
            ->join('appointments', 'appointments.id', '=', 'google_meetings.appointment_id')
            ->where('google_meeting_call_logs.is_initial', 1)
            ->where('google_meetings.patient_id', $patientId)
            ->whereIn('appointments.appointment_statuses_id', [$completedId, $visitCreatedId])
            ->selectRaw('COUNT(DISTINCT google_meetings.appointment_id) as count')
            ->value('count');
    }

    private function getPatientRingCentralAppointmentCount(int $patientId)
    {
        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();

        return RingcentralCallLog::query()
            ->join('appointments', 'appointments.id', '=', 'ringcentral_call_logs.appointment_id')
            ->where('ringcentral_call_logs.patient_id', $patientId)
            ->whereIn('appointments.appointment_statuses_id', [$completedId, $visitCreatedId])
            ->selectRaw('COUNT(DISTINCT ringcentral_call_logs.appointment_id) as count')
            ->value('count');
    }

    public function getPatientStatusData(int $patientId)
    {
        $patientData = Patient::select('status_updated_at', 'status_id')->find($patientId);
    
        if ($patientData->status_id == PatientStatus::getDischargedId()) {
            return $this->getPatientDischargedData($patientId);
        }

        return [
            'date' => isset($patientData->status_updated_at) ? Carbon::parse($patientData->status_updated_at)->format('m/d/Y') : null,
        ];
    }

    private function getPatientDischargedData(int $patientId)
    {
        $patientDischargedDataDocument = PatientDocument::select('updated_at')
            ->where('patient_id', $patientId)
            ->whereIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeDischarge())
            ->latest()
            ->first();

        $patientDischargedDataElectronicDocument = PatientElectronicDocument::select('provider_id', 'updated_at')
            ->where('patient_id', $patientId)
            ->whereIn('document_type_id', AssessmentForm::getFileTypeIDsLikeDischarge())
            ->latest()
            ->first();

        if ($patientDischargedDataDocument && $patientDischargedDataElectronicDocument) {
            if (strtotime($patientDischargedDataDocument->updated_at) > strtotime($patientDischargedDataElectronicDocument->updated_at)) {
                return [
                  'date' => $patientDischargedDataDocument->updated_at->format('m/d/Y'),
                ];
            }
            return [
                'date' => $patientDischargedDataElectronicDocument->updated_at->format('m/d/Y'),
                'provider' => $this->getProviderNameById($patientDischargedDataElectronicDocument->provider_id),
            ];
        }
        if ($patientDischargedDataDocument) {
            return [
               'date' => $patientDischargedDataDocument->updated_at->format('m/d/Y'),
            ];
        }
        if ($patientDischargedDataElectronicDocument) {
            return [
                'date' => $patientDischargedDataElectronicDocument->updated_at->format('m/d/Y'),
                'provider' => $this->getProviderNameById($patientDischargedDataElectronicDocument->provider_id),
            ];
        }

        return [];
    }

    private function getProviderNameById(int $id): ?string
    {
        $provider = Provider::query()
            ->withTrashed()
            ->select('provider_name')
            ->where('id', $id)
            ->first();

        return optional($provider)->provider_name;
    }
    
    private function getVisitAverageDuration(int $patientId)
    {
        $googleMeetAverageDuration = $this->getGoogleMeetAverageDuration($patientId);
        $ringCentralAverageDuration = $this->getRingCentralAverageDuration($patientId);

        if ($googleMeetAverageDuration && $ringCentralAverageDuration) {
            return ($googleMeetAverageDuration + $ringCentralAverageDuration) / 2;
        }
        if ($googleMeetAverageDuration) {
            return $googleMeetAverageDuration;
        }
        if ($ringCentralAverageDuration) {
            return $ringCentralAverageDuration;
        }

        return 0;
    }

    private function getGoogleMeetAverageDuration(int $patientId)
    {
        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();

        $durationLogs = GoogleMeeting::query()
            ->selectRaw('(SUM(google_meeting_call_logs.duration) / 60) as duration')
            ->join('google_meeting_call_logs', 'google_meeting_call_logs.google_meeting_id', '=', 'google_meetings.id')
            ->join('appointments', 'appointments.id', '=', 'google_meetings.appointment_id')
            ->where('google_meetings.patient_id', $patientId)
            ->where('google_meeting_call_logs.is_external', 0)
            ->whereIn('appointments.appointment_statuses_id', [$completedId, $visitCreatedId])
            ->groupBy('google_meetings.appointment_id')
            ->get();

        return $durationLogs->count() ? $durationLogs->sum('duration') / $durationLogs->count() : 0;
    }

    private function getRingCentralAverageDuration(int $patientId)
    {
        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();

        $durationLogsMapping = RingcentralCallLog::query()
            ->select(['appointment_id', 'call_starts_at', 'call_ends_at'])
            ->join('appointments', 'appointments.id', '=', 'ringcentral_call_logs.appointment_id')
            ->where('ringcentral_call_logs.patient_id', $patientId)
            ->whereNotNull('ringcentral_call_logs.call_starts_at')
            ->whereNotNull('ringcentral_call_logs.call_ends_at')
            ->whereIn('appointments.appointment_statuses_id', [$completedId, $visitCreatedId])
            ->get()
            ->reduce(function ($carry, $item) {
                $callStartTime = Carbon::createFromFormat("Y-m-d H:i:s", $item->call_starts_at);
                $callEndTime = Carbon::createFromFormat("Y-m-d H:i:s", $item->call_ends_at);
                $duration = $callEndTime->diffInMinutes($callStartTime);
                $carry[$item->appointment_id] = isset($carry[$item->appointment_id]) ? $carry[$item->appointment_id] + $duration : $duration;

                return $carry;
            }, []);

        return count($durationLogsMapping) ? array_sum(array_values($durationLogsMapping)) / count($durationLogsMapping) : 0;
    }
}
