<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Jobs\Salary\AssignVisitsToAppointments;
use App\Models\Billing\BillingPeriod;
use App\Models\Provider\SalaryTimesheet;
use App\Models\Provider\SalaryTimesheetVisit;
use App\PatientVisit;
use Carbon\Carbon;
use Exception;

class PatientVisitObserver
{
    public function updating(PatientVisit $visit): void
    {
        if (
            $visit->appointment_id
            && $visit->plan_id
            && $visit->procedure_id
            && $visit->isDirty(['appointment_id', 'plan_id', 'procedure_id', 'is_telehealth', 'is_overtime'])
        ) {
            $visit->needs_update_salary = 1;
        }

        if ($visit->provider_id && !$visit->provider_tariff_plan_id) {
            $this->assignProviderTariffPlanId($visit, false);
        }
    }

    /**
     * @throws Exception
     */
    public function deleting(PatientVisit $visit): void
    {
        $visit->appointment_id = null;
        $visit->needs_update_salary = 1;
        $visit->save();
        /** @var SalaryTimesheetVisit $timesheetRecord */
        $timesheetRecord = SalaryTimesheetVisit::query()->where('visit_id', $visit->getKey())->first();

        if (
            $timesheetRecord
            && !$timesheetRecord->reviewed_at
            && !$timesheetRecord->accepted_at
            && !$timesheetRecord->declined_at
        ) {
            $timesheetRecord->delete();
        }
    }

    public function restoring(PatientVisit $visit): void
    {
        $visit->needs_update_salary = 1;
    }

    public function restored(PatientVisit $visit): void
    {
        $this->assignAppointments($visit);
        $this->generateTimesheet($visit);

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($visit),
                'event' => 'restore',
                'data' => $visit->getLogData(),
                'message' => $visit->getRestoreLogMessage(),
            ]
        );
    }

    public function created(PatientVisit $visit): void
    {
        $this->assignAppointments($visit);

        if ($visit->provider_id && !$visit->provider_tariff_plan_id) {
            $this->assignProviderTariffPlanId($visit);
        }

        $this->generateTimesheet($visit);

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($visit),
                'event' => 'create',
                'data' => $visit->getLogData(),
                'message' => $visit->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientVisit $visit): void
    {
        if ($visit->isDirty(['date', 'is_overtime', 'patient_id', 'provider_id'])) {
            $this->generateTimesheet($visit);
        }

        $dirtyFields = $visit->getDirtyWithOriginal();

        if (count($dirtyFields)) {
            HIPAALogger::logEvent(
                [
                    'collection' => class_basename($visit),
                    'event' => 'update',
                    'data' => $visit->getLogData(),
                    'dirty_fields' => $dirtyFields,
                    'message' => $visit->getUpdateLogMessage($dirtyFields),
                ]
            );
        }
    }

    public function deleted(PatientVisit $visit): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($visit),
                'event' => 'delete',
                'data' => $visit->getLogData(),
                'message' => $visit->getDeleteLogMessage(),
            ]
        );
    }

    private function generateTimesheet(PatientVisit $visit): void
    {
        $visit->refresh();

        if ($visit->salary_timesheet_visit_id) {
            return;
        }

        if (!$visit->provider_id) {
            return;
        }

        if (!$visit->patient_id) {
            return;
        }

        if (!$visit->date) {
            return;
        }

        $timesheetRecord = SalaryTimesheetVisit::query()->where('visit_id', $visit->getKey())->withTrashed()->first();

        if (optional($timesheetRecord)->provider_reviewed_at && optional($timesheetRecord)->trashed()) {
            return;
        }

        if (optional($timesheetRecord)->provider_reviewed_at) {
            return;
        }

        if (optional($timesheetRecord)->accepted_at) {
            return;
        }

        if (optional($timesheetRecord)->declined_at) {
            return;
        }

        if ($timesheetRecord) {
            $timesheetRecord->update([
                'date' => $visit->date,
                'is_overtime' => $visit->is_overtime,
                'is_telehealth' => $visit->is_telehealth,
                'patient_id' => $visit->patient_id,
                'provider_id' => $visit->provider_id,
                'deleted_at' => null,
            ]);
        } else {
            $billingPeriod = BillingPeriod::getBillingPeriodByDate(
                Carbon::parse($visit->date),
                $visit->provider()->withTrashed()->first()->billing_period_type_id
            );
            SalaryTimesheetVisit::create([
                'visit_id' => $visit->getKey(),
                'billing_period_id' => $billingPeriod->getKey(),
                'patient_id' => $visit->patient_id,
                'provider_id' => $visit->provider_id,
                'date' => $visit->date,
                'is_overtime' => $visit->is_overtime,
                'is_telehealth' => $visit->is_telehealth,
                'is_custom_created' => false,
            ]);
            SalaryTimesheet::firstOrCreate([
                'provider_id'       => $visit->provider_id,
                'billing_period_id' => $billingPeriod->getKey(),
            ], [
                'seek_time'                    => 0,
                'monthly_meeting_attended'     => false,
                'changed_appointment_statuses' => false,
                'completed_ia_and_pn'          => false,
                'set_diagnoses'                => false,
                'completed_timesheet'          => false,
            ]);
        }
    }

    private function assignAppointments(PatientVisit $visit): void
    {
        $startDate = Carbon::parse($visit->date)->startOfDay();
        $endDate = $startDate->copy()->endOfDay();

        dispatch(new AssignVisitsToAppointments($startDate, $endDate));
    }

    private function assignProviderTariffPlanId(PatientVisit $visit, bool $save = true): void
    {
        // ToDo: can be replaced with Illuminate\Support\Facades\DB?
        $tariffPlan = \DB::table('providers_tariffs_plans')->where('provider_id', $visit->provider_id)->first();
        $visit->provider_tariff_plan_id = optional($tariffPlan)->tariff_plan_id;

        if ($save) {
            $visit->save();
        }
    }
}
