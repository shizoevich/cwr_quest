<?php

namespace Tests\Feature\Parsers;

use App\Appointment;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\PatientsParser;
use App\Option;
use App\Patient;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\OfficeAlly\PatientsOfficeAllyHelper;
use Tests\TestCase;
use Tests\Traits\AppointmentTrait;
use Tests\Traits\KaiserAppointmentTrait;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use Tests\Traits\PatientTrait;

class PatientsParserTest extends TestCase
{
    use OfficeAllyTrait;
    use AppointmentTrait;
    use PatientTrait;
    use KaiserAppointmentTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_2;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testOfficeAllyAuthorization()
    {
        Event::fake();

        self::authorizationTest($this, self::OA_ACCOUNT);
    }

    public function testPatientListDataStructure(): void
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_3);

        $patientListRaw = $officeAllyHelper->getPatientList([
            [
                'field' => 'SearchBy',
                'data' => 'PatientID',
            ],
            [
                'field' => 'SearchCondition',
                'data' => 0,
            ],
            [
                'field' => 'TextField',
                'data' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            ],
        ]);

        $patientListData = PatientsOfficeAllyHelper::getPatientListDataFromRaw($patientListRaw);

        foreach ($patientListData as $patientListItemData) {
            $this->assertDataStructure($patientListItemData, PatientsOfficeAllyHelper::getStructurePatientListItemData());
        }
    }

    public function testCreatePatients()
    {
        Event::fake();

        $patientListRaw = PatientsOfficeAllyHelper::getPatientListDataRaw();

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientList', $patientListRaw);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        $this->assertDatabaseMissing(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            ]
        );

        (new PatientsParser('parser', false))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            ]
        );
    }

    public function testUpdatePatients()
    {
        Event::fake();

        $testFirstName = '!test_first_name!';
        $testLastName = '!test_last_name!';

        self::generatePatient([
            'patient_id' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            'first_name' => $testFirstName,
            'last_name' => $testLastName,
        ]);

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
                'first_name' => $testFirstName,
                'last_name' => $testLastName,
            ]
        );

        $patientListRaw = PatientsOfficeAllyHelper::getPatientListDataRaw();

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientList', $patientListRaw);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientsParser('parser', false))->handleParser();

        $this->assertDatabaseMissing(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
                'first_name' => $testFirstName,
                'last_name' => $testLastName,
            ]
        );

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
                'first_name' => PatientsOfficeAllyHelper::OA_PATIENT_FIRST_NAME,
                'last_name' => PatientsOfficeAllyHelper::OA_PATIENT_LAST_NAME,
            ]
        );
    }

    public function testUpdateKaiserAppointments()
    {
        Event::fake();

        $appointment = factory(Appointment::class)->create();

        $this->generateKaiserAppointment([
            'internal_id' => $appointment->id,
            'patient_id' => null,
            'first_name' => PatientsOfficeAllyHelper::OA_PATIENT_FIRST_NAME,
            'last_name' => PatientsOfficeAllyHelper::OA_PATIENT_LAST_NAME,
            'date_of_birth' => PatientsOfficeAllyHelper::OA_PATIENT_DATE_OF_BIRTH,
        ]);

        $this->assertDatabaseHas(
            self::TABLE_KAISER_APPOINTMENTS,
            [
                'patient_id' => null,
                'first_name' => PatientsOfficeAllyHelper::OA_PATIENT_FIRST_NAME,
                'last_name' => PatientsOfficeAllyHelper::OA_PATIENT_LAST_NAME,
                'date_of_birth' => PatientsOfficeAllyHelper::OA_PATIENT_DATE_OF_BIRTH,
            ]
        );

        $patientListRaw = PatientsOfficeAllyHelper::getPatientListDataRaw();

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientList', $patientListRaw);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientsParser('parser', false))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            ]
        );

        $patient = Patient::where('patient_id', PatientsOfficeAllyHelper::OA_PATIENT_PATIENT_ID)->first();

        $this->assertDatabaseHas(
            self::TABLE_KAISER_APPOINTMENTS,
            [
                'patient_id' => $patient->id,
                'first_name' => PatientsOfficeAllyHelper::OA_PATIENT_FIRST_NAME,
                'last_name' => PatientsOfficeAllyHelper::OA_PATIENT_LAST_NAME,
                'date_of_birth' => PatientsOfficeAllyHelper::OA_PATIENT_DATE_OF_BIRTH,
            ]
        );
    }
}