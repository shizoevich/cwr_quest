<?php

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
  public function run(): void
  {
    $this->call(RolesSeeder::class);
    $this->call(ProviderSeeder::class);
    $this->call(PatientInsuranceSeeder::class);
    $this->call(InsurancesProceduresSeed::class);
    $this->call(PatientStatusesSeeder::class);
    $this->call(StatusesSeeder::class);
    $this->call(ApointmentsTableSeeder::class);
    $this->call(BillingPeriodTypeSeeder::class);
    $this->call(OfficeTableSeeder::class);
    $this->call(RolesSeeder::class);
    $this->call(PatientTableSeeder::class);

    $this->call(InsuranceTableSeeder::class);
    $this->call(PatientVisitsStatusTableSeeder::class);
    $this->call(PlanTableSeeder::class);
    $this->call(ProcedureTableSeeder::class);
    $this->call(ProviderTariffTableSeeder::class);
    $this->call(ReasonTableSeeder::class);
    $this->call(ParserSeeder::class);

    $this->call(LanguageSeeder::class);
    $this->call(ParserSeeder::class);
    $this->call(OptionsTableSeeder::class);
    $this->call(PatientDocumentTypeSeeder::class);
  }
}
