<?php

namespace App\Console\Commands\Salary;

use App\Models\Provider\SalaryTimesheet;
use App\Models\Provider\SalaryTimesheetVisit;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;

class SyncTimesheetWithVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:salary-timesheet-with-visits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create SalaryTimesheet records';

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
        $records = SalaryTimesheetVisit::query()
            ->select(['salary_timesheet_visits.provider_id', 'salary_timesheet_visits.billing_period_id'])
            ->distinct()
            ->leftJoin('salary_timesheets', function (JoinClause $join) {
                $join->on('salary_timesheets.provider_id', 'salary_timesheet_visits.provider_id')
                    ->on('salary_timesheets.billing_period_id', 'salary_timesheet_visits.billing_period_id');
            })
            ->whereNull('salary_timesheets.id')
            ->toBase()
            ->get();

        foreach ($records as $record) {
            SalaryTimesheet::firstOrCreate([
                'provider_id'       => $record->provider_id,
                'billing_period_id' => $record->billing_period_id,
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
}
