<?php

namespace App\Console\Commands\SalaryCheck;

use App\Appointment;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Models\Provider\Salary;
use App\Option;
use App\PatientNote;
use App\PatientVisit;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompareOfficeAllyVisitsCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-salary:compare-visits-count';
    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare count of visits in office ally';
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
        $officeAlly = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_3);

        $billingPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);

        $startDate = Carbon::parse($billingPeriod->start_date)->format('n/j/Y');
        $endDate = Carbon::parse($billingPeriod->end_date)->endOfDay()->format('n/j/Y g:i:s A');

        $this->info($startDate . ' ' . $endDate);

        $providers = Provider::query()
            ->select(['id', 'provider_name', 'officeally_id'])
            ->withCount([
                'salary' => function ($query) use ($billingPeriod) {
                    $query->where('billing_period_id', $billingPeriod->id)
                        ->whereNotNull('visit_id')
                        ->whereNotIn('type', Salary::REFUND_TYPES);
                },
            ])
            ->whereNotNull('officeally_id')
            ->where('billing_period_type_id', BillingPeriodType::getBiWeekly()->id)
            ->orderBy('provider_name')
            ->get();

        foreach ($providers as $provider) {
            $officeAllyVisitsCount = $officeAlly->getVisitListCount([
                [
                    'field' => 'StartDate',
                    'data' => $startDate,
                ],
                [
                    'field' => 'EndDate',
                    'data' => $endDate,
                ],
                [
                    'field' => 'ProviderID',
                    'data' => $provider->officeally_id,
                ]
            ]);

            $visitsCount = $provider->salary_count;

            $this->info('Provider: ' . $provider->provider_name . '; Visits count - CWR: ' . $visitsCount . ', OA: ' . $officeAllyVisitsCount);

            if ($visitsCount !== $officeAllyVisitsCount) {
                $this->warn('Warning! Different count of visits between CWR and OA for provider: ' . $provider->provider_name);
            }
        }
    }
}
