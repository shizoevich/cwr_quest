<?php

use App\Option;
use Illuminate\Database\Seeder;

class OptionsTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->getOptions()->each(function($option) {
            Option::firstOrCreate(array_only($option, 'option_name'), $option);
        });

//        delete unused records
        Option::where('option_name', '=', 'patient_stop_auth_parsing_status')->delete();
    }
    
    private function getOptions()
    {
        return collect([
            [
                //TODO: delete
                'option_name' => 'parsing_status',
                'option_value' => '0',
            ],
            [
                'option_name' => 'doctor_password',
                'option_value' => bcrypt('123456'),
            ],
            [
                'option_name' => 'parsing_visits',
                'option_value' => '0',
            ],
            [
                'option_name' => 'system_password',
                'option_value' => \Hash::make('48526'),
            ],
            [
                'option_name' => 'parser_error_mail_enabled',
                'option_value' => 0,
            ],
            [
                'option_name' => 'google_service_account_credentials',
                'option_value' => '{}',
            ],
            [
                'option_name' => 'google_service_account_subject',
                'option_value' => 'admin@example.com',
            ],
            [
                'option_name' => 'officeally_credentials',
                'option_value' => env('OFFICE_ALLY_CREDENTIALS'),
            ],
            [
                'option_name' => 'tridiuum_credentials',
                'option_value' => json_encode([
                    'sgershelis' => [
                        'login' => 'sgershelis',
                        'password' => encrypt('secret'),
                    ],
                ]),
            ],
            [
                'option_name' => 'ringcentral_credentials',
                'option_value' => '{}',
            ],
        ]);
    }
}
