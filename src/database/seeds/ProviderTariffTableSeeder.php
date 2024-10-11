<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderTariffTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tariffs_plans')->insert([
            [
                'name' => 'Payout Plan 2 (Master Level)',
                'fee_per_missing_pn' => 1500,
            ],
            [
                'name' => 'Payout Plan 1 (Doctors Level)',
                'fee_per_missing_pn' =>  1500,
            ],
            [
                'name' => 'Payout Plan 1 (Doctors Level) - Custom - William Kaiser, Ph.D.',
                'fee_per_missing_pn' =>  1500,
            ],
            [
                'name' => 'Payout Plan  for MFT',
                'fee_per_missing_pn' =>  1500,
            ],
            [
                'name' => 'Payout Plan 1 (Doctors Level)Frank',
                'fee_per_missing_pn' =>  1500,
            ],
        ]);
    }
}
