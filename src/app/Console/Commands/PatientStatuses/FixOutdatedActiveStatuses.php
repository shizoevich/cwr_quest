<?php

namespace App\Console\Commands\PatientStatuses;

use App\AssessmentForm;
use App\Patient;
use App\PatientDocumentType;
use App\PatientStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixOutdatedActiveStatuses extends Command
{
    const TO_DISCHARGED = 'to_discharged';
    const ACTIVE_TO_INACTIVE = 'active_to_inactive';
    const INACTIVE_TO_LOST = 'inactive_to_lost';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:fix-outdated-active-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Patient::query()
            ->statusActive()
            ->chunk(1000, function ($patients) {
                foreach ($patients as $patient) {
                    $lastAppointment = $patient->lastCompletedVisitCreatedAppointment;

                    if (!isset($lastAppointment)) {
                        $this->changePatientStatusWithoutAppointments($patient);
                    } else {
                        $this->changePatientStatusWithAppointments($patient, Carbon::createFromTimestamp($lastAppointment->time));
                    }
                }
            });
    }

    private function changePatientStatusWithoutAppointments(Patient $patient)
    {
        $dischargedDate = $this->getPatientDischargedDate($patient);

        if (isset($dischargedDate)) {
            PatientStatus::changeStatusAutomatically($patient->id, self::TO_DISCHARGED);
        } else {
            PatientStatus::changeStatusAutomatically($patient->id, self::ACTIVE_TO_INACTIVE);
            PatientStatus::changeStatusAutomatically($patient->id, self::INACTIVE_TO_LOST);
        }
    }

    private function changePatientStatusWithAppointments(Patient $patient, Carbon $lastAppointmentDate)
    {
        $dischargedDate = $this->getPatientDischargedDate($patient);

        if (isset($dischargedDate) && $dischargedDate->greaterThan($lastAppointmentDate)) {
            PatientStatus::changeStatusAutomatically($patient->id, self::TO_DISCHARGED);
        } else {
            $this->handleInactiveAndLostPatient($patient, $lastAppointmentDate);
        }
    }

    private function handleInactiveAndLostPatient(Patient $patient, Carbon $lastAppointmentDate)
    {
        $now = Carbon::now();
        $diffInDays = $lastAppointmentDate->diffInDays($now, false);
        $activeToInactivePeriod = PatientStatus::getChangeStatusPeriod($lastAppointmentDate, self::ACTIVE_TO_INACTIVE, $patient->visit_frequency_id);

        if ($diffInDays >= $activeToInactivePeriod) {
            PatientStatus::changeStatusAutomatically($patient->id, self::ACTIVE_TO_INACTIVE);
        }

        $inactiveToLostPeriod = PatientStatus::getChangeStatusPeriod($lastAppointmentDate, self::INACTIVE_TO_LOST, $patient->visit_frequency_id);

        if ($diffInDays >= $inactiveToLostPeriod) {
            PatientStatus::changeStatusAutomatically($patient->id, self::INACTIVE_TO_LOST);
        }
    }

    private function getPatientDischargedDate(Patient $patient)
    {
        $patientDischargedDataDocument = $patient->documents()
            ->select('updated_at')
            ->whereIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeDischarge())
            ->latest()
            ->first();

        $patientDischargedDataElectronicDocument = $patient->electronicDocuments()
            ->select('updated_at')
            ->whereIn('document_type_id', AssessmentForm::getFileTypeIDsLikeDischarge())
            ->latest()
            ->first();

        if ($patientDischargedDataDocument && $patientDischargedDataElectronicDocument) {
            if (strtotime($patientDischargedDataDocument->updated_at) > strtotime($patientDischargedDataElectronicDocument->updated_at)) {
                return Carbon::parse($patientDischargedDataDocument->updated_at);
            }
            return Carbon::parse($patientDischargedDataElectronicDocument->updated_at);
        }

        if ($patientDischargedDataDocument) {
            return Carbon::parse($patientDischargedDataDocument->updated_at);
        }
        if ($patientDischargedDataElectronicDocument) {
            return Carbon::parse($patientDischargedDataElectronicDocument->updated_at);
        }

        return null;
    }
}
