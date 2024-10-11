<?php

namespace App\Repositories\Provider\Salary\Timesheet;

use App\Models\Billing\BillingPeriod;
use App\Models\Provider\ProviderSupervisor;
use App\Models\Provider\Salary;
use App\Models\Provider\SalaryTimesheet;
use App\Models\Provider\SalaryTimesheetLateCancellation;
use App\Models\Provider\SalaryTimesheetSupervision;
use App\Models\Provider\SalaryTimesheetVisit;
use App\Provider;
use App\TherapistSurvey;
use App\User;
use Carbon\Carbon;
use App\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TimesheetRepository implements TimesheetRepositoryInterface
{
    public function visits(Provider $provider): array
    {
        if (!$provider->billingPeriodType) {
            return [];
        }
        $billingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        if (!$billingPeriod) {
            return [];
        }

        return $provider->salaryTimesheetVisits()->select([
            'salary_timesheet_visits.*',
            'patients.first_name',
            'patients.last_name',
            'patients.id AS patient_id',
            \DB::raw("IF(`appointments`.`time` IS NULL, 1, 0) AS ordering"),
            \DB::raw("from_unixtime(appointments.time) as tmp"),
            'appointments.id AS appointment_id',
            'appointments.is_initial',
            'appointments.note_on_paper',
            'appointments.initial_assessment_id',
            'appointments.initial_assessment_created_at',
            'patient_notes.id AS patient_note_id',
            'patient_notes.finalized_at AS note_finalized_at',
        ])
            ->join('patients', 'patients.id', '=', 'salary_timesheet_visits.patient_id')
            ->leftJoin('patient_visits', 'patient_visits.id', '=', 'salary_timesheet_visits.visit_id')
            ->leftJoin('appointments', 'appointments.id', '=', 'patient_visits.appointment_id')
            ->leftJoin('patient_notes', function ($join) {
                $join->on('patient_notes.appointment_id', '=', 'appointments.id')
                    ->whereNull('patient_notes.deleted_at');
            })
            ->where('salary_timesheet_visits.billing_period_id', $billingPeriod->getKey())
            ->orderBy('date')
            ->orderBy('ordering')
            ->orderBy('appointments.time')
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->get()
            ->transform(function ($timesheetVisit) use ($provider, $billingPeriod) {
                if (!$timesheetVisit->appointment_id
                    || $timesheetVisit->note_on_paper
                    || $timesheetVisit->initial_assessment_id
                    || $timesheetVisit->progress_note_complete
                ) {
                    $timesheetVisit->is_progress_note_missing = false; 
                } else {
                    $docBillingPeriod = null;
                    if ($timesheetVisit->note_finalized_at) {
                        $docBillingPeriod = BillingPeriod::getBillingPeriodByDate(Carbon::parse($timesheetVisit->note_finalized_at), $provider->billing_period_type_id);
                    } else if ($timesheetVisit->initial_assessment_created_at) {
                        $docBillingPeriod = BillingPeriod::getBillingPeriodByDate(Carbon::parse($timesheetVisit->initial_assessment_created_at), $provider->billing_period_type_id);
                    }

                    $timesheetVisit->is_progress_note_missing = !$docBillingPeriod || $docBillingPeriod->getKey() != $billingPeriod->getKey();
                }

                return $timesheetVisit;
            })
            ->groupBy('date')
            ->toArray();
    }

    public function getTimesheet(Provider $provider, BillingPeriod $billingPeriod = null)
    {
        if (!$provider->billingPeriodType) {
            return null;
        }
        if (!$billingPeriod) {
            $billingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        }
        if (!$billingPeriod) {
            return null;
        }

        return SalaryTimesheet::query()
            ->where('provider_id', $provider->getKey())
            ->where('billing_period_id', $billingPeriod->getKey())
            ->first();
    }

    public function needsRedirect(Provider $provider)
    {
        if (!$provider->billingPeriodType) {
            return false;
        }

        $prevBillingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        if (!$prevBillingPeriod) {
            return false;
        }
        
        $timesheetSubmitRequiredDate = Carbon::parse($prevBillingPeriod->end_date)->addDay()->startOfDay()->addHours(config('timesheet.submit_required_gap'));
        if (Carbon::now()->gt($timesheetSubmitRequiredDate)) {
            $timesheet = $this->getTimesheet($provider, $prevBillingPeriod);

            return empty($timesheet) || empty($timesheet->signed_at);
        }

        return false;
    }

    public function isEditingAllowed(Provider $provider): bool
    {
        if (!$provider->billingPeriodType) {
            return false;
        }

        $prevBillingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        if (!$prevBillingPeriod) {
            return false;
        }
        
        $timesheetAllowedEditingDate = Carbon::parse($prevBillingPeriod->end_date)->addDay()->startOfDay()->addHours(config('timesheet.allowed_editing_gap'));
        if (Carbon::now()->gt($timesheetAllowedEditingDate)) {
            $timesheet = $this->getTimesheet($provider, $prevBillingPeriod);

            return empty($timesheet) || empty($timesheet->signed_at);
        }

        return false;
    }

    public function lateCancellations(Provider $provider): array
    {
        if (!$provider->billingPeriodType) {
            return [];
        }
        $billingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        if (!$billingPeriod) {
            return [];
        }

        return $provider->salaryTimesheetLateCancellations()->select([
            'salary_timesheet_late_cancellations.*',
            'patients.first_name',
            'patients.last_name',
            'patients.id AS patient_id',
        ])
            ->join('patients', 'patients.id', '=', 'salary_timesheet_late_cancellations.patient_id')
            ->where('salary_timesheet_late_cancellations.billing_period_id', $billingPeriod->getKey())
            ->orderBy('salary_timesheet_late_cancellations.date')
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->get()
            ->groupBy('date')
            ->toArray();
    }

    public function supervisions(Provider $provider): array
    {
        $billingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        $supervisees = ProviderSupervisor::getSuperviseesForPeriod($provider->id, Carbon::parse($billingPeriod->start_date), Carbon::parse($billingPeriod->end_date));
        
        foreach ($supervisees as $supervisee) {
            $supervisionRecord = SalaryTimesheetSupervision::query()
                ->where([
                    'provider_id' => $supervisee->provider_id,
                    'supervisor_id' => $supervisee->supervisor_id,
                    'billing_period_id' => $billingPeriod->id
                ])
                ->first();

            $supervisee->supervision_hours = 0;
            $supervisee->comment = '';

            if (isset($supervisionRecord)) {
                $supervisee->supervision_hours = $supervisionRecord->supervision_hours;
                $supervisee->comment = $supervisionRecord->comment;
            }
        }

        return [$supervisees->toArray()];
    }

    public function modifyVisits(array $data)
    {
        $this->deleteVisits(__data_get($data, 'delete', []));
        $this->editVisits(__data_get($data, 'edit', []));
        $this->createVisits(__data_get($data, 'create', []));
        $this->markVisitsAsReviewed($data['billing_period_id']);
    }

    private function markVisitsAsReviewed($billingPeriodId)
    {
        SalaryTimesheetVisit::query()
            ->withTrashed()
            ->where('billing_period_id', $billingPeriodId)
            ->where('provider_id', auth()->user()->provider_id)
            ->whereNull('provider_reviewed_at')
            ->update(['provider_reviewed_at' => Carbon::now()]);
    }

    public function modifyLateCancellations(array $data)
    {
        $this->deleteLateCancellations(__data_get($data, 'delete', []));
        $this->createLateCancellations(__data_get($data, 'create', []));
    }

    public function modifySupervisions(int $billingPeriodId, array $data)
    {
        foreach ($data as $item) {
            SalaryTimesheetSupervision::updateOrCreate([
                'billing_period_id' => $billingPeriodId,
                'provider_id' => $item['provider_id'],
                'supervisor_id' => $item['supervisor_id']
            ], [
                'supervision_hours' => $item['supervision_hours'],
                'comment' => $item['comment'] ?? null,
            ]);
        }
    }

    public function deleteVisits(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        SalaryTimesheetVisit::query()->whereIn('id', $ids)->where('provider_id', auth()->user()->provider_id)->delete();
    }

    public function deleteLateCancellations(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        SalaryTimesheetLateCancellation::query()->whereIn('id', $ids)->where(
            'provider_id',
            auth()->user()->provider_id
        )->delete();
    }

    public function editVisits(array $data)
    {
        foreach ($data as $item) {
            SalaryTimesheetVisit::query()->whereKey($item['id'])->where(
                'provider_id',
                auth()->user()->provider_id
            )->update([
                'is_overtime' => $item['is_overtime'],
                'is_custom_created' => true,
            ]);
        }
    }

    public function createVisits(array $data)
    {
        foreach ($data as $item) {
            SalaryTimesheetVisit::query()->create([
                'billing_period_id'    => $item['billing_period_id'],
                'patient_id'           => $item['patient_id'],
                'provider_id'          => auth()->user()->provider_id,
                'date'                 => $item['date'],
                'is_overtime'          => $item['is_overtime'],
                'is_telehealth'        => $item['is_telehealth'],
                'is_custom_created'    => true,
                'provider_reviewed_at' => Carbon::now(),
            ]);
        }
    }

    public function createLateCancellations(array $data)
    {
        foreach ($data as $item) {
            SalaryTimesheetLateCancellation::query()->create([
                'billing_period_id' => $item['billing_period_id'],
                'patient_id'        => $item['patient_id'],
                'provider_id'       => auth()->user()->provider_id,
                'date'              => $item['date'],
                'amount'            => $item['amount'],
                'is_custom_created' => true,
            ]);
        }
    }

    public function complete(array $data)
    {
        SalaryTimesheet::query()->updateOrCreate([
            'provider_id'       => auth()->user()->provider_id,
            'billing_period_id' => $data['billing_period_id'],
        ], [
            'seek_time'                    => 0,
            'monthly_meeting_attended'     => $data['monthly_meeting_attended'] ?? false,
            'complaint'                    => $data['complaint'] ?? '',
            'changed_appointment_statuses' => true,
            'completed_ia_and_pn'          => true,
            'set_diagnoses'                => true,
            'completed_timesheet'          => true,
            'signed_at'                    => Carbon::now(),
        ]);
    }

    public function deleteLateCancellationsFromTimeSheets(int $id)
    {
        $lateCancellation = SalaryTimesheetLateCancellation::find($id);

        $salary = Salary::where('provider_id', $lateCancellation->provider_id)
            ->where('billing_period_id', $lateCancellation->billing_period_id)
            ->get();
        $salaryLateCancellationIdArray = $salary->pluck('additional_data.salary_timesheet_late_cancellation_id')->toArray();
        $salaryIdArray = $salary->pluck('id')->toArray();
        $arraySalaryIdAndLateCancellationId = array_combine($salaryIdArray, $salaryLateCancellationIdArray);
        $searchSalaryKey = array_search($id, $arraySalaryIdAndLateCancellationId);

        $salaryId = Salary::find($searchSalaryKey);
        $salaryId->delete();

        $lateCancellation->delete();
        return new JsonResponse(
            [
                'message' => "delete salary and salary_timesheet_late_cancellation by id",
                'status' => Response::HTTP_OK,
            ],
            200
        );
    }

    public function getSickHours(Provider $provider, Carbon $startDate, Carbon $endDate)
    {
        return SalaryTimesheet::query()
            ->join('billing_periods', 'billing_periods.id', '=', 'salary_timesheets.billing_period_id')
            ->where('salary_timesheets.provider_id', '=', $provider->id)
            ->whereDate('billing_periods.start_date', '>=', $startDate->toDateString())
            ->whereDate('billing_periods.start_date', '<', $endDate->toDateString())
            ->sum('salary_timesheets.seek_time');
    }

    public function getRemainingSickHours(Provider $provider, Carbon $startDate, Carbon $endDate)
    {
        $sickHours = $this->getSickHours($provider, $startDate, $endDate);
        $sickHoursPerYear = Option::getOptionValue('sick_hours_per_year');
        $remainingSickHours = $sickHoursPerYear - $sickHours;

        return $remainingSickHours < 0 ? 0 : $remainingSickHours;
    }

    public function calcSupervisorCompensation(SalaryTimesheet $salaryTimesheet)
    {
        $totalHours = SalaryTimesheetSupervision::query()
            ->where('billing_period_id', $salaryTimesheet->billing_period_id)
            ->where('supervisor_id', $salaryTimesheet->provider_id)
            ->sum('supervision_hours');

        if (!$totalHours) {
            return;
        }

        $fee = $totalHours * Salary::SUPERVISOR_HOUR_PRICE;

        Salary::query()
            ->updateOrCreate([
                'provider_id' => $salaryTimesheet->provider_id,
                'billing_period_id' => $salaryTimesheet->billing_period_id,
                'type' => Salary::TYPE_SUPERVISOR_COMPENSATION,
            ], [
                'fee' => $fee,
                'paid_fee' => $fee,
                'date' => Carbon::parse($salaryTimesheet->billingPeriod->end_date)->toDateString(),
                'additional_data' => ['visit_count' => $totalHours],
            ]);
    }

    public function calcSuperviseeCompensation(SalaryTimesheet $supervisorSalaryTimesheet)
    {
        $supervisorId = $supervisorSalaryTimesheet->provider_id;
        $billingPeriod = $supervisorSalaryTimesheet->billingPeriod;
        $billingPeriodId = $billingPeriod->id;

        $startDate = Carbon::parse($billingPeriod->start_date);
        $endDate = Carbon::parse($billingPeriod->end_date);

        ProviderSupervisor::getSuperviseesForPeriod($supervisorId, $startDate, $endDate)
            ->each(function ($supervisee) use ($billingPeriodId, $endDate) {
                $providerId = $supervisee->provider_id;

                $totalHours = SalaryTimesheetSupervision::query()
                    ->where('billing_period_id', $billingPeriodId)
                    ->where('provider_id', $providerId)
                    ->sum('supervision_hours');

                if (!$totalHours) {
                    return;
                }

                $fee = $totalHours * Salary::SUPERVISEE_HOUR_PRICE;

                Salary::query()
                    ->updateOrCreate([
                        'provider_id' => $providerId,
                        'billing_period_id' => $billingPeriodId,
                        'type' => Salary::TYPE_SUPERVISEE_COMPENSATION,
                    ], [
                        'fee' => $fee,
                        'paid_fee' => $fee,
                        'date' => $endDate->toDateString(),
                        'additional_data' => ['visit_count' => $totalHours],
                    ]);
            });
    }
}
