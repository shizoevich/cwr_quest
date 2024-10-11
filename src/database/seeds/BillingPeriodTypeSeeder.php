<?php

use App\Models\Billing\BillingPeriodType;
use Illuminate\Database\Seeder;

class BillingPeriodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'name' => BillingPeriodType::TYPE_BI_WEEKLY,
                'title' => 'Bi-Weekly',
            ],
            [
                'name' => BillingPeriodType::TYPE_MONTHLY,
                'title' => 'Monthly',
            ]
        ];
        
        foreach ($types as $type) {
            BillingPeriodType::query()->updateOrCreate(array_only($type, 'name'), $type);
        }
    }
}
