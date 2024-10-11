<?php

use App\Option;
use Illuminate\Database\Seeder;

class GmailApiCredentialsTableSeeder extends Seeder
{
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
                'option_name' => 'gmail_api_credentials',
                'option_value' => '{}',
            ],
            [
                'option_name' => 'gmail_api_token',
                'option_value' => '{}',
            ],
        ]);
    }
}
