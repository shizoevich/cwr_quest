<?php

namespace App\Repositories\Provider\Salary\Timesheet;

use App\Models\Provider\SalaryTimesheet;
use App\Models\Billing\BillingPeriod;
use App\Provider;
use Carbon\Carbon;

interface TimesheetRepositoryInterface
{
    /**
     * @param Provider $provider
     *
     * @return array
     */
    public function visits(Provider $provider): array;
    
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function modifyVisits(array $data);
    
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function modifyLateCancellations(array $data);

    /**
     * @param int $billingPeriodId
     * 
     * @param array $data
     *
     * @return mixed
     */
    public function modifySupervisions(int $billingPeriodId, array $data);

    /**
     * @param Provider $provider
     *
     * @return array
     */
    public function lateCancellations(Provider $provider): array;

    /**
     * @param Provider $provider
     *
     * @return array
     */
    public function supervisions(Provider $provider): array;

    /**
     * Remove the specified resource from table salary_timesheet_late_cancellations by id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteLateCancellationsFromTimeSheets(int $id);
    
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function complete(array $data);
    
    /**
     * @param Provider $provider
     *
     * @return bool
     */
    public function isEditingAllowed(Provider $provider): bool;
    
    public function getTimesheet(Provider $provider, BillingPeriod $billingPeriod = null);
    
    public function needsRedirect(Provider $provider);

    public function getSickHours(Provider $provider, Carbon $startDate, Carbon $endDate);

    public function getRemainingSickHours(Provider $provider, Carbon $startDate, Carbon $endDate);

    public function calcSupervisorCompensation(SalaryTimesheet $salaryTimesheet);

    public function calcSuperviseeCompensation(SalaryTimesheet $salaryTimesheet);
}
