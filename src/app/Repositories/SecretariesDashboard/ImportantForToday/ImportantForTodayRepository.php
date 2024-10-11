<?php

namespace App\Repositories\SecretariesDashboard\ImportantForToday;

use App\Appointment;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Patient;
use App\PatientDocument;
use App\PatientStatus;
use App\PatientInsurance;
use App\Models\Patient\PatientPreprocessedTransaction;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\PatientDocumentType;
use App\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportantForTodayRepository implements ImportantForTodayRepositoryInterface
{
    private const REQUIRED_FORMS = [
        'new_patient',
        'payment_for_service',
        'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights'
    ];

    private const ELIGIBILITY_CHECK_BY = 90;

    /**
     * Shows appointments with patients without signed forms.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public function getAppointmentsWithoutForms(Carbon $startDate, Carbon $endDate): array
    {
        $patientStatusArchivedId = PatientStatus::getArchivedId();
        $requiredFormTypeIds = PatientFormType::getFormTypeIds(self::REQUIRED_FORMS);

        $patientsWithoutForms = Patient::query()
            ->select('patients.id')
            ->where('patients.status_id', '!=', $patientStatusArchivedId)
            ->whereNotCompletedIntakeForms()
            ->get()
            ->pluck('id');

        $appointments = $this->getBaseAppointmentQuery($startDate, $endDate)
            ->whereIn('patients_id', $patientsWithoutForms)
            ->withPatientAppointmentsCount()
            ->get();

        $this->loadPatientFirstVisitDate($appointments);
        $this->loadPatientLastDocumentRequest($appointments, $requiredFormTypeIds);

        return [
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
            ],
        ];
    }

    /**
     * Shows appointments with patients without checked eligibility more than self::ELIGIBILITY_CHECK_BY days.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public function getAppointmentsWithRequiredEligibility(Carbon $startDate, Carbon $endDate): array
    {
        $startOfYear = Carbon::now()->startOfYear();
        $eligibilityCheckByDate = Carbon::now()->subDays(self::ELIGIBILITY_CHECK_BY);
        $startCheckingDate = $eligibilityCheckByDate->lte($startOfYear)
            ? $startOfYear
            : $eligibilityCheckByDate;
        $patientStatusArchivedId = PatientStatus::getArchivedId();
        $lastEligibilityCheckedDateSql = 'SELECT patient_id, MAX(date_created) as max_date FROM patient_alerts GROUP BY patient_id';
        $lastAlertOfEachPatientSql = "SELECT patient_id, MAX(id) as last_alert_id FROM patient_alerts WHERE date_created > {$startCheckingDate->toDateString()} GROUP BY patient_id";

        $patientsWithRequiredEligibility = Patient::query()
            ->select('patients.id')
            ->where('patients.status_id', '!=', $patientStatusArchivedId)
            ->leftJoin(
                DB::raw("($lastEligibilityCheckedDateSql) as pa"),
                'patients.id',
                '=',
                'pa.patient_id'
            )
            ->where(function ($query) use ($startCheckingDate) {
                $query->whereNull('pa.max_date')
                    ->orWhereDate('pa.max_date', '<=', $startCheckingDate->toDateString());
            })
            ->get()
            ->pluck('id');

        $patientsWithDeductible = Patient::query()
            ->select('patients.id')
            ->where('patients.status_id', '!=', $patientStatusArchivedId)
            ->join(
                DB::raw("($lastAlertOfEachPatientSql) as la"),
                'patients.id',
                '=',
                'la.patient_id'
            )
            ->join('patient_alerts', 'patient_alerts.id', '=', 'la.last_alert_id')
            ->where('patient_alerts.deductible', '>', 0)
            ->whereRaw('`patient_alerts`.`deductible_met` < `patient_alerts`.`deductible`')
            ->get()
            ->pluck('id');

        $patientIds = $patientsWithRequiredEligibility->merge($patientsWithDeductible)->unique()->values();

        $appointments = $this->getBaseAppointmentQuery($startDate, $endDate)
            ->whereIn('appointments.patients_id', $patientIds)
            ->with('patient.alert')
            ->get();

        return [
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
            ],
        ];
    }

    public function getAppointmentsWithDeductible(Carbon $startDate, Carbon $endDate): array
    {
        $startOfYear = Carbon::now()->startOfYear();
        $eligibilityCheckByDate = Carbon::now()->subDays(self::ELIGIBILITY_CHECK_BY);
        $startCheckingDate = $eligibilityCheckByDate->lte($startOfYear)
            ? $startOfYear
            : $eligibilityCheckByDate;
        $patientStatusArchivedId = PatientStatus::getArchivedId();
        $lastAlertOfEachPatientSql = "SELECT patient_id, MAX(id) as last_alert_id FROM patient_alerts WHERE date_created > {$startCheckingDate->toDateString()} GROUP BY patient_id";

        $patientsWithDeductible = Patient::query()
            ->select('patients.id')
            ->where('patients.status_id', '!=', $patientStatusArchivedId)
            ->join(
                DB::raw("($lastAlertOfEachPatientSql) as la"),
                'patients.id',
                '=',
                'la.patient_id'
            )
            ->join('patient_alerts', 'patient_alerts.id', '=', 'la.last_alert_id')
            ->where('patient_alerts.deductible', '>', 0)
            ->whereRaw('`patient_alerts`.`deductible_met` < `patient_alerts`.`deductible`')
            ->get()
            ->pluck('id');

        $appointments = $this->getBaseAppointmentQuery($startDate, $endDate)
            ->whereIn('appointments.patients_id', $patientsWithDeductible)
            ->with(['patient.alert' => function ($query) {
                $query->orderBy('id', 'desc');
            }])
            ->get();
            
        $this->loadPatientCreditCard($appointments);

        return [
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
            ],
        ];
    }

    /**
     * Shows appointments with patients that have no other appointment in the future.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public function getPatientLastAppointments(Carbon $startDate, Carbon $endDate): array
    {
        $patientStatusArchivedId = PatientStatus::getArchivedId();
        $patientStatusDischargedId = PatientStatus::getDischargedId();
        $patientsLastAppointmentsSql = 'SELECT patients_id as patient_id, MAX(time) as max_time FROM appointments WHERE time >= ' . $startDate->timestamp . ' GROUP BY patients_id';

        $appointments = $this->getBaseAppointmentQuery($startDate, $endDate)
            ->withPatientAppointmentsCount()
            ->join(
                DB::raw("($patientsLastAppointmentsSql) as lpa"),
                function ($join) {
                    $join->on('appointments.patients_id', '=', 'lpa.patient_id');
                    $join->on('appointments.time', '=', 'lpa.max_time');
                }
            )
            ->whereHas('patient', function ($query) use ($patientStatusDischargedId, $patientStatusArchivedId) {
                $query->whereNotIn('status_id', [$patientStatusDischargedId, $patientStatusArchivedId]);
            })
            ->get();

        $this->loadPatientFirstVisitDate($appointments);

        return [
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
            ],
        ];
    }

    /**
     * Shows appointments with patients that have negative balance.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public function getAppointmentsWithNegativeBalance(Carbon $startDate, Carbon $endDate): array
    {
        $patientStatusArchivedId = PatientStatus::getArchivedId();
        $lastTransactionOfEachPatientSql = 'SELECT MAX(id) as last_transaction_id FROM patient_preprocessed_transactions GROUP BY patient_id';

        $patientsWithNegativeBalance = PatientPreprocessedTransaction::query()
            ->select('patient_preprocessed_transactions.patient_id')
            ->join(
                DB::raw("($lastTransactionOfEachPatientSql) as lt"),
                'patient_preprocessed_transactions.id',
                '=',
                'lt.last_transaction_id'
            )
            ->where('patient_preprocessed_transactions.balance_after_transaction', '<', 0)
            ->whereHas('patient', function ($query) use ($patientStatusArchivedId) {
                $query->where('status_id', '!=', $patientStatusArchivedId);
            })
            ->get()
            ->pluck('patient_id');

        $appointments = $this->getBaseAppointmentQuery($startDate, $endDate)
            ->whereIn('patients_id', $patientsWithNegativeBalance)
            ->with('patient.preprocessedBalance')
            ->withPatientAppointmentsCount()
            ->get();

        $this->loadPatientFirstVisitDate($appointments);
        $this->loadPatientCreditCard($appointments);

        return [
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
            ],
        ];
    }

    /**
     * Shows appointments with patients that have cash insurance.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public function getAppointmentsWithCash(Carbon $startDate, Carbon $endDate): array
    {
        $patientStatusArchivedId = PatientStatus::getArchivedId();

        $appointments = $this->getBaseAppointmentQuery($startDate, $endDate)
            ->whereHas('patient', function ($patientQuery) use ($patientStatusArchivedId) {
                $patientQuery->where('status_id', '!=', $patientStatusArchivedId)
                    ->where('primary_insurance_id', PatientInsurance::getCashId());
            })
            ->get();

        $this->loadPatientFirstVisitDate($appointments);
        $this->loadPatientCreditCard($appointments);

        return [
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
            ],
        ];
    }

    /**
     * Loads has_credit_card property for patients of Appointment collection.
     *
     * @param Collection $appointments
     * @return void
     */
    private function loadPatientCreditCard(Collection $appointments): void
    {
        $patientsWithCards = Patient::query()
            ->select('patients.id as id')
            ->join('patient_square_accounts as psa', 'patients.id', '=', 'psa.patient_id')
            ->join('patient_square_account_cards as psac', 'psa.id', '=', 'psac.patient_square_account_id')
            ->whereIn('patients.id', $appointments->pluck('patients_id'))
            ->get();

        $appointments->each(function ($appointment) use ($patientsWithCards) {
            $patientHasCreditCard = $patientsWithCards->contains('id', $appointment->patients_id);

            $appointment->patient->credit_card = $patientHasCreditCard
                ? $appointment->patient->getCreditCardWithFurthestExpirationDate()
                : null;
        });
    }

    /**
     * Loads first_visit_date property for patients of Appointment collection.
     *
     * @param Collection $appointments
     * @return void
     */
    private function loadPatientFirstVisitDate(Collection $appointments): void
    {
        $patientsFirstAppointmentsDates = Appointment::query()
            ->select([
                'patients_id as id',
                DB::raw('DATE(FROM_UNIXTIME(MIN(time))) as first_visit_date'),
            ])
            ->whereIn('patients_id', $appointments->pluck('patients_id'))
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->groupBy(['patients_id'])
            ->get();

        $appointments->each(function ($appointment) use ($patientsFirstAppointmentsDates) {
            $patientFirstAppointmentDate = $patientsFirstAppointmentsDates
                ->where('id', $appointment->patients_id)
                ->first();

            $appointment->patient->first_visit_date = $patientFirstAppointmentDate->first_visit_date ?? null;
        });
    }

    private function loadPatientLastDocumentRequest(Collection $appointments, array $requiredFormTypes): void
    {
        $documentRequests = PatientDocumentRequest::query()
            ->whereIn('patient_id', $appointments->unique('patients_id')->pluck('patients_id'))
            ->with([
                'items' => function ($query) use ($requiredFormTypes) {
                    $query->whereIn('form_type_id', $requiredFormTypes);
                }
            ])
            ->whereHas('items', function ($query) use ($requiredFormTypes) {
                $query->whereIn('form_type_id', $requiredFormTypes);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        $appointments->each(function ($appointment) use ($documentRequests) {
            $requests = $documentRequests->where('patient_id', $appointment->patients_id);

            $appointment->patient->last_document_request = $requests->count() !== 0
                ? $requests->first()
                : null;
        });
    }

    private function getBaseAppointmentQuery(Carbon $startDate, Carbon $endDate): Builder
    {
        return Appointment::query()->forPeriod($startDate, $endDate);
    }
}