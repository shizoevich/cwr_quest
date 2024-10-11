<?php

namespace App\Console\Commands\SingleUse;

use App\Appointment;
use App\Models\Billing\BillingPeriod;
use App\PatientVisit;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

class CheckLostVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'check:lost-visits {billing_period_id} {provider_id}';
    protected $signature = 'check:lost-visits {billing_period_id} {provider_id} {count_data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '
    {billing_period_id}  - set billing period id
    {provider_id}  - provider id
    {count_data} - set true or false, if set value - true, console command will show only count data, how many appointments you have lost
    ';

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
        $providerId = $this->argument('provider_id');
        $billingPeriodData = BillingPeriod::where('id', $this->argument('billing_period_id'))->first();
        $count_data = $this->argument('count_data');

        $visitAppointmentData = Appointment::where('providers_id', $providerId)
            ->where('deleted_at', NULL)
            ->whereBetween('start_completing_date', [$billingPeriodData->start_date, $billingPeriodData->end_date])
            ->whereIn('appointment_statuses_id', [1, 5]);

        $visitCountData =  PatientVisit::where('provider_id', $providerId)
            ->where('deleted_at', NULL)
            ->whereBetween('date', [$billingPeriodData->start_date, $billingPeriodData->end_date]);

        if ($count_data == 1) {
            $visitAppointmentCount = $visitAppointmentData->count();
            $visitCount = $visitCountData->count();
            $lostVisits =   $visitAppointmentCount - $visitCount;
            print_r('Lost visits' . ':' . $lostVisits . '     ');
        } else {
            $visitAppointmentArrayOfId = $visitAppointmentData->pluck('id');
            $visitData =  $visitCountData->whereNotIn('appointment_id', $visitAppointmentArrayOfId)->get()->toArray();
            print_r($visitData);
        }
    }
}
