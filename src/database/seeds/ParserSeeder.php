<?php

use Illuminate\Database\Seeder;

class ParserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->getParsers()->each(function($parser) {
            \App\Models\Parser::query()->updateOrCreate([
                'name' => $parser['name'],
                'service' => $parser['service']
            ], $parser);
        });
    }
    
    private function getParsers()
    {
        return collect([
            [
                'service' => \App\Models\Parser::SERVICE_OFFICEALLY,
                'name' => 'appointments',
                'title' => 'Appointments',
                'description' => '',
                'allow_manual_start' => true,
            ],
            [
                'service' => \App\Models\Parser::SERVICE_OFFICEALLY,
                'name' => 'patients',
                'title' => 'Patients',
                'description' => '',
                'allow_manual_start' => true,
            ],
            [
                'service' => \App\Models\Parser::SERVICE_OFFICEALLY,
                'name' => 'visits',
                'title' => 'Visits',
                'description' => '',
                'allow_manual_start' => true,
            ],
            [
                'service' => \App\Models\Parser::SERVICE_OFFICEALLY,
                'name' => 'providers',
                'title' => 'Therapists',
                'description' => '',
                'allow_manual_start' => true,
            ],
            [
                'service' => \App\Models\Parser::SERVICE_OFFICEALLY,
                'name' => 'payments',
                'title' => 'Payments',
                'description' => '',
                'allow_manual_start' => true,
            ],
            [
                'service' => \App\Models\Parser::SERVICE_OFFICEALLY,
                'name' => 'diagnoses',
                'title' => 'Diagnoses',
                'description' => '',
                'allow_manual_start' => true,
            ],
        ]);
    }
}
