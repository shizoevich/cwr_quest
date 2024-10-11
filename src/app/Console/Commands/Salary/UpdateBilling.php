<?php

namespace App\Console\Commands\Salary;

use App\Appointment;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Models\Provider\Salary;
use App\PatientNote;
use App\PatientVisit;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:update {providerId}';
    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = '';
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
        $providerId = $this->argument('providerId');
        $previousBillingPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY)->toArray();
        $startDatePreviousBillingPeriod = $previousBillingPeriod['start_date'];
        $endDatePreviousBillingPeriod = $previousBillingPeriod['end_date'];

        Salary::where('type','=','4')
            ->whereBetween(
                'date',
                [$startDatePreviousBillingPeriod, $endDatePreviousBillingPeriod]
            )
            ->where('provider_id', $providerId)
            ->whereColumn('fee', '!=', 'paid_fee')
            ->each(function ($salaryBilling) {
                $patientVisitQuery = PatientVisit::where('id', $salaryBilling->visit_id);
                $appointmentId = $patientVisitQuery->first()->appointment_id;
                $patientNote = PatientNote::where('appointment_id', $appointmentId)->first();
                if (
                    ($patientNote->is_finalized != 1)
                    && (!isset($patientNote->finalized_at))
                ) {
                    Appointment::where('id', $appointmentId)->update([
                        'progress_note_complete' => true
                    ]);

                    $patientVisitQuery->update([
                        'needs_update_salary' => '0',
                        'is_update_salary_enabled' => '0'
                    ]);

                    $salaryBilling->where('id', $salaryBilling->id)->update([
                        'paid_fee' => $salaryBilling->fee,
                        'type' => '2',
                    ]);

                    Salary::where('visit_id', $salaryBilling->visit_id)
                                  ->where('type','=','6')
                                  ->update([
                        'deleted_at' => Carbon::now(),
                    ]);
                }
            });
    }
}
