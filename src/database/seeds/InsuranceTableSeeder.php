<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsuranceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('patient_insurances')->insert([
            [
                'insurance' => 'Kaiser Permanente',
            ],
            [
                'insurance' => 'Beacon Health Strategies',
            ],
            [
                'insurance' => 'BC001 Blue Cross of California',
            ],
            [
                'insurance' => 'CIGNA  Global Health Benefits',
            ],
            [
                'insurance' => 'Blue Shield - California / Blue Shield MHSA',
            ],
        ]);
    }
}
