<?php

namespace App\Console\Commands\SingleUse;

use App\Models\Billing\BillingPeriod;
use App\Models\Provider\SalaryTimesheetVisit;
use App\Patient;
use App\PatientStatus;
use App\PatientVisit;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncSalaryTimesheetVisits extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timesheet-visits:sync';

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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PatientVisit::query()
            ->where('date', '>=', '2021-04-01')
            ->each(function(PatientVisit $visit) {
                if(!$visit->patient_id || !$visit->provider_id || !$visit->date) {
                    return;
                }
                $billingPeriod = BillingPeriod::getBillingPeriodByDate(Carbon::parse($visit->date), $visit->provider()->withTrashed()->first()->billing_period_type_id);
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
            });
    }
}
