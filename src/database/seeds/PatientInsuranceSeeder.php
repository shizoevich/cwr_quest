<?php

use App\PatientInsurance;
use Illuminate\Database\Seeder;

class PatientInsuranceSeeder extends Seeder
{
    public function run(): void
    {
        factory(PatientInsurance::class, 1)->create();
    }
}
