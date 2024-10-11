<?php

use App\Models\Patient\Inquiry\PatientInquiryRegistrationMethod;
use Illuminate\Database\Seeder;

class PatientInquiryRegistrationMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $methods = ["Incoming call", "Website", "Email", "Chat", "Fax"];

        foreach($methods as $method) {
            PatientInquiryRegistrationMethod::create(['name' => $method]);
        }
    }
}
