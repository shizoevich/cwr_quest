<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('patient_insurances_plans')->insert([
            [
                'insurance_id' => 1,
                'name' => 'HMO',
                'need_collect_copay_for_telehealth' => 0,
                'parent_id' => 244,
            ],
            [
                'insurance_id' => 1,
                'name' => 'Gold Coast',
                'need_collect_copay_for_telehealth' => 0,
                'parent_id' => 245,
            ],
            [
                'insurance_id' => 1,
                'name' => 'EAP',
                'need_collect_copay_for_telehealth' => 0,
                'parent_id' => 246,
            ],
            [
                'insurance_id' => 1,
                'name' => 'HMO',
                'need_collect_copay_for_telehealth' => 0,
                'parent_id' => 247,
            ],
            [
                'insurance_id' => 1,
                'name' => 'DHMO',
                'need_collect_copay_for_telehealth' => 0,
                'parent_id' => 248,
            ],
            [
                'insurance_id' => 1,
                'name' => 'FEP',
                'need_collect_copay_for_telehealth' => 0,
                'parent_id' => 249,
            ],
        ]);
    }
}
