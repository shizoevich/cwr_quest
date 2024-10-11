<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PatientDefaultCommentsSeeder::class);
        $this->call(OptionsTableSeeder::class);
        $this->call(AssessmentFormsSeeder::class);
        $this->call(DocumentsTypeDefaultAddresses::class);
        $this->call(PatientStatusesSeeder::class);
        $this->call(PatientDocumentTypesSeeder::class);
        $this->call(SharedDocumentMethodsSeeder::class);
        $this->call(SharedDocumentStatusesSeeder::class);
        $this->call(TypeFormSeeder::class);
        $this->call(InsurancesProceduresSeed::class);
        $this->call(ExamsSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(PatientFormTypeSeeder::class);
        $this->call(BillingPeriodTypeSeeder::class);
        $this->call(ParserSeeder::class);
        $this->call(AppointmentRescheduleSubStatusesSeeder::class);
    }
}
