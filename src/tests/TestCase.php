<?php

namespace Tests;

use App\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    use RefreshDatabase;

    //DB tables
    protected const TABLE_PATIENTS = 'patients';
    protected const TABLE_DIAGNOSES = 'diagnoses';
    protected const TABLE_PATIENT_DIAGNOSES = 'patient_diagnoses';
    protected const TABLE_PATIENT_DIAGNOSES_OLD = 'patient_diagnoses_old';
    protected const TABLE_PATIENT_HAS_PROVIDERS = 'patients_has_providers';
    protected const TABLE_PATIENT_TEMPLATES = 'patient_templates';
    protected const TABLE_PATIENT_COMMENTS = 'patient_comments';
    protected const TABLE_PATIENT_INSURANCES = 'patient_insurances';
    protected const TABLE_PATIENT_INSURANCES_PLANS = 'patient_insurances_plans';
    protected const TABLE_PATIENT_INSURANCES_PROCEDURES = 'patient_insurances_procedures';
    protected const TABLE_FAXES = 'faxes';
    protected const TABLE_FAX_COMMENTS = 'fax_comments';
    protected const TABLE_FAX_STATUSES = 'fax_statuses';
    protected const TABLE_FAX_LOG_ACTIVITIES = 'user_fax_log_activities';
    protected const TABLE_USERS = 'users';
    protected const TABLE_PROVIDERS = 'providers';
    protected const TABLE_APPOINTMENTS = 'appointments';
    protected const TABLE_APPOINTMENT_STATUSES = 'appointment_statuses';
    protected const TABLE_APPOINTMENT_NOTIFICATIONS = 'appointment_notifications';
    protected const TABLE_RINGCENTRAL_CALL = 'ringcentral_call_logs';
    protected const TABLE_PATIENT_DOCUMENTS = 'patient_documents';
    protected const TABLE_OFFICES = 'offices';    
    protected const TABLE_OFFICES_ROOMS = 'office_rooms';
    protected const TABLE_RINGCENTAL_CALL_LOGS = 'ringcentral_call_logs';
    protected const TABLE_BILLING_PROVIDERS = 'billing_providers';
    protected const TABLE_OPTIONS = 'options';
    protected const TABLE_KAISER_APPOINTMENTS = 'kaiser_appointments';
    protected const TABLE_ELIGIBILITY_PAYERS = 'eligibility_payers';
    protected const TABLE_OFFICEALLY_TRANSACTIONS = 'officeally_transactions';
    protected const TABLE_OFFICEALLY_TRANSACTION_TYPES = 'officeally_transaction_types';
    protected const TABLE_PATIENT_VISITS = 'patient_visits';
    protected const TABLE_PATIENT_VISIT_STATUSES = 'patient_visit_statuses';
    protected const TABLE_PATIENT_VISIT_DIAGNOSES = 'patient_visit_diagnoses';
    protected const TABLE_VISIT_REASONS = 'visit_reasons';

    protected function refreshTestDatabase()
    {
        if (! RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh');

            $this->generateDefaultData();

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        } 

        $this->beginDatabaseTransaction();
    }

    private function generateDefaultData(): void
    {
        Artisan::call('db:seed', ['--class' => 'TestDatabaseSeeder']);
    }

    protected function generateErrorMessages(...$errorData): array
    {
        return array_merge_recursive(...$errorData);
    }

    protected function assertDataStructure(array $data, array $structure): void
    {
        foreach ($structure as $key => $value) {
            $this->assertArrayHasKey($key, $data, "The key '$key' is missing in the given data.");
            if (is_array($value)) {
                $this->assertDataStructure($data[$key], $value);
            } else {
                if ($data[$key]) {
                    $this->assertInternalType($value, $data[$key], "The value of '$key' does not correspond to type '$value'.");
                }
            }
        }
    }
}
