<?php

namespace Tests\Feature\Parsers;

use App\Appointment;
use App\AppointmentNotification;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Officeally\Retry\RetryDeleteAppointment;
use App\Jobs\Parsers\Guzzle\AppointmentsParser;
use App\Models\PatientHasProvider;
use App\Office;
use App\OfficeRoom;
use App\Option;
use App\Patient;
use App\PatientDocumentType;
use App\PatientStatus;
use App\Provider;
use App\Status;
use Carbon\Carbon;
use Tests\Helpers\OfficeAlly\AppointmentsOfficeAllyHelper;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use App\Jobs\Notifications\RingcentralSms;

class AppointmentsParserTest extends TestCase
{
    use OfficeAllyTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_3;

    protected const ID_FOR_TEST = 1;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testOfficeAllyAuthorization()
    {
        Event::fake();

        self::authorizationTest($this, self::OA_ACCOUNT);
    }

    public function testAppointmentDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::findNotEmptyAppointmentsPage($officeAllyHelper);
        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataFromAppointmentsHtml($appointmentsHtml);

        foreach (AppointmentsOfficeAllyHelper::getStructureAppointmentData() as $key => $type) {
            $this->assertArrayHasKey($key, $appointmentData, "The key '$key' is missing in the appointment data.");
            $this->assertInternalType($type, $appointmentData[$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testPatientDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::findNotEmptyAppointmentsPage($officeAllyHelper);
        $patientData = AppointmentsOfficeAllyHelper::getPatientDataFromAppointmentsHtml($appointmentsHtml);

        foreach (AppointmentsOfficeAllyHelper::getStructurePatientData() as $key => $type) {
            $this->assertArrayHasKey($key, $patientData, "The key '$key' is missing in the patient data.");
            $this->assertInternalType($type, $patientData[$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testCreateOffices()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Office::where('office', $officeData['office'])->delete();

        $this->assertDatabaseMissing(self::TABLE_OFFICES, ['office' => $officeData['office']]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_OFFICES, [
            'office' => $officeData['office'],
            'external_id' => $officeData['external_id']
        ]);
    }

    public function testUpdateOffices()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Office::where('office', $officeData['office'])->update(['office' => 'Test Office']);

        $this->assertDatabaseMissing(self::TABLE_OFFICES, [
            'office' => $officeData['office'],
            'external_id' => $officeData['external_id']
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_OFFICES, [
            'office' => $officeData['office'],
            'external_id' => $officeData['external_id']
        ]);
    }
    
    public function testCreateStatuses()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Status::where('status', $appointmentData['status'])->delete();

        $this->assertDatabaseMissing(self::TABLE_APPOINTMENT_STATUSES, [
            'status' => $appointmentData['status'],
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_APPOINTMENT_STATUSES, [
            'status' => $appointmentData['status'],
        ]);
    }

    public function testCreatePatients()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $this->assertDatabaseMissing(self::TABLE_PATIENTS, [
            'patient_id' => $appointmentData['patient_id'],
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENTS, [
            'patient_id'             => $appointmentData['patient_id'],
            'patient_account_number' => $appointmentData['patient_account_number'],
            'auth_number'            => $appointmentData['auth_number'],
            'first_name'             => $appointmentData['first_name'],
            'last_name'              => $appointmentData['last_name'],
            'middle_initial'         => $appointmentData['middle_initial'],
            'insured_name'           => $appointmentData['insured_name'],
            'secondary_insured_name' => $appointmentData['secondary_insured_name'],
            'address'                => $appointmentData['address'],
            'cell_phone'             => $appointmentData['cell_phone'],
            'home_phone'             => $appointmentData['home_phone'],
            'work_phone'             => $appointmentData['work_phone'],
            'visits_auth'            => $appointmentData['visits_auth'],
            'visits_auth_left'       => 1,
            'primary_insurance'      => $appointmentData['primary_insurance'],
            'secondary_insurance'    => $appointmentData['secondary_insurance'],
            'sex'                    => $appointmentData['sex'],
            // 'elig_copay'             => $appointmentData['elig_copay'],
            'elig_status'            => $appointmentData['elig_status'],
            'reffering_provider'     => $appointmentData['reffering_provider'],
            // 'visit_copay'            => $appointmentData['visit_copay'] * 100,
        ]);
    }
    
    public function testCreateOfficeRooms()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $roomName = AppointmentsOfficeAllyHelper::getRoomNameFromResource($appointmentData['resource']);

        OfficeRoom::where('name', $roomName)->delete();

        $office = Office::where('office', $appointmentData['office'])->first();

        $this->assertDatabaseMissing(self::TABLE_OFFICES_ROOMS, [
            'name' => $roomName,
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_OFFICES_ROOMS, [
            'name' => $roomName,
            'office_id' => $office->id
        ]);
    }

    public function testCreateProviders()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $this->assertDatabaseMissing(self::TABLE_PROVIDERS, [
            'provider_name' => $appointmentData['provider_name'],
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PROVIDERS, [
            'provider_name' => $appointmentData['provider_name'],
        ]);
    }

    public function testCreateAppointments()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $this->assertDatabaseMissing(self::TABLE_APPOINTMENTS, [
            'idAppointments' => $appointmentData['idAppointments'],
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $provider = Provider::where('provider_name', $appointmentData['provider_name'])->first();
        $patient = Patient::where('patient_id', $appointmentData['patient_id'])->first();
        $office = Office::where('office', $appointmentData['office'])->first();
        $officeRoom = OfficeRoom::where(
            'name',
            AppointmentsOfficeAllyHelper::getRoomNameFromResource($appointmentData['resource'])
        )->first();
        $status = Status::where('status', $appointmentData['status'])->first();

        $this->assertDatabaseHas(
            self::TABLE_APPOINTMENTS,
            [
                'time'             => $appointmentData['time'],
                'idAppointments'   => $appointmentData['idAppointments'],
                'resource'         => $appointmentData['resource'],
                'visit_copay'      => $appointmentData['visit_copay'] * 100,
                'visit_length'     => $appointmentData['visit_length'],
                'notes'            => $appointmentData['notes'],
                'reason_for_visit' => $appointmentData['reason_for_visit'],
                'sheldued_by'      => $appointmentData['sheldued_by'],
                'date_created'     => $appointmentData['date_created'],
                'check_in'         => $appointmentData['check_in'],
                'not_found_count'  => 0,
                'providers_id'     => $provider->getKey(),
                'patients_id'      => $patient->getKey(),
                'offices_id'       => $office->getKey(),
                'office_room_id'   => optional($officeRoom)->getKey(),
                'appointment_statuses_id' => $status->getKey(),
            ]
        );
    }
    
    public function testUpdateAppointmentIfNoRetryJobsExistQueue()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Appointment::find(self::ID_FOR_TEST)->update(['idAppointments', $appointmentData]);

        $checkingData = [
            'time'             => $appointmentData['time'],
            'idAppointments'   => $appointmentData['idAppointments'],
            'resource'         => $appointmentData['resource'],
            'visit_copay'      => $appointmentData['visit_copay'] * 100,
            'visit_length'     => $appointmentData['visit_length'],
            'notes'            => $appointmentData['notes'],
            'reason_for_visit' => $appointmentData['reason_for_visit'],
            'sheldued_by'      => $appointmentData['sheldued_by'],
            'date_created'     => $appointmentData['date_created'],
            'check_in'         => $appointmentData['check_in'],
            'not_found_count'  => 0,
        ];

        $this->assertDatabaseMissing(self::TABLE_APPOINTMENTS, $checkingData);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $provider = Provider::where('provider_name', $appointmentData['provider_name'])->first();
        $patient = Patient::where('patient_id', $appointmentData['patient_id'])->first();
        $office = Office::where('office', $appointmentData['office'])->first();
        $officeRoom = OfficeRoom::where(
            'name',
            AppointmentsOfficeAllyHelper::getRoomNameFromResource($appointmentData['resource'])
        )->first();
        $status = Status::where('status', $appointmentData['status'])->first();

        $checkingData = array_merge($checkingData, [
            'providers_id'     => $provider->getKey(),
            'patients_id'      => $patient->getKey(),
            'offices_id'       => $office->getKey(),
            'office_room_id'   => optional($officeRoom)->getKey(),
            'appointment_statuses_id' => $status->getKey()
        ]);

        $this->assertDatabaseHas(self::TABLE_APPOINTMENTS, $checkingData);
    }
    
    public function testUpdateAppointmentIfRetryJobsExistQueue()
    {
        Event::fake();

        $appointmentId = self::ID_FOR_TEST;

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $appointment = Appointment::find($appointmentId);
        $appointment->idAppointments = $appointmentData['idAppointments'];
        $appointment->save();

        $checkingData = [
            'time'             => $appointmentData['time'],
            'idAppointments'   => $appointmentData['idAppointments'],
            'resource'         => $appointmentData['resource'],
            'visit_copay'      => $appointmentData['visit_copay'] * 100,
            'visit_length'     => $appointmentData['visit_length'],
            'notes'            => $appointmentData['notes'],
            'reason_for_visit' => $appointmentData['reason_for_visit'],
            'sheldued_by'      => $appointmentData['sheldued_by'],
            'date_created'     => $appointmentData['date_created'],
            'check_in'         => $appointmentData['check_in'],
            'not_found_count'  => 0,
        ];

        $this->assertDatabaseMissing(self::TABLE_APPOINTMENTS, $checkingData);

        dispatch((new RetryDeleteAppointment(self::OA_ACCOUNT, $appointmentId))->delay(Carbon::now()->addSeconds(3600)));

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $provider = Provider::where('provider_name', $appointmentData['provider_name'])->first();
        $patient = Patient::where('patient_id', $appointmentData['patient_id'])->first();
        $office = Office::where('office', $appointmentData['office'])->first();
        $officeRoom = OfficeRoom::where(
            'name',
            AppointmentsOfficeAllyHelper::getRoomNameFromResource($appointmentData['resource'])
        )->first();
        $status = Status::where('status', $appointmentData['status'])->first();

        $checkingData = array_merge($checkingData, [
            'providers_id'     => $provider->getKey(),
            'patients_id'      => $patient->getKey(),
            'offices_id'       => $office->getKey(),
            'office_room_id'   => optional($officeRoom)->getKey(),
            'appointment_statuses_id' => $status->getKey()
        ]);

        $this->assertDatabaseMissing(self::TABLE_APPOINTMENTS, $checkingData);
    }

    public function testRestoreTrashedAppointment()
    {
        Event::fake();

        $appointmentId = self::ID_FOR_TEST;

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $appointment = Appointment::find($appointmentId);
        $appointment->idAppointments = $appointmentData['idAppointments'];
        $appointment->save();

        $appointment->delete();

        $checkingData = [
            'time'             => $appointmentData['time'],
            'idAppointments'   => $appointmentData['idAppointments'],
            'resource'         => $appointmentData['resource'],
            'visit_copay'      => $appointmentData['visit_copay'] * 100,
            'visit_length'     => $appointmentData['visit_length'],
            'notes'            => $appointmentData['notes'],
            'reason_for_visit' => $appointmentData['reason_for_visit'],
            'sheldued_by'      => $appointmentData['sheldued_by'],
            'date_created'     => $appointmentData['date_created'],
            'check_in'         => $appointmentData['check_in'],
            'not_found_count'  => 0,
        ];

        $this->assertSoftDeleted(
            self::TABLE_APPOINTMENTS,
            ['idAppointments' => $appointmentData['idAppointments']]
        );

        $this->assertDatabaseMissing(self::TABLE_APPOINTMENTS, $checkingData);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $provider = Provider::where('provider_name', $appointmentData['provider_name'])->first();
        $patient = Patient::where('patient_id', $appointmentData['patient_id'])->first();
        $office = Office::where('office', $appointmentData['office'])->first();
        $officeRoom = OfficeRoom::where(
            'name',
            AppointmentsOfficeAllyHelper::getRoomNameFromResource($appointmentData['resource'])
        )->first();
        $status = Status::where('status', $appointmentData['status'])->first();

        $checkingData = array_merge($checkingData, [
            'providers_id'     => $provider->getKey(),
            'patients_id'      => $patient->getKey(),
            'offices_id'       => $office->getKey(),
            'office_room_id'   => optional($officeRoom)->getKey(),
            'appointment_statuses_id' => $status->getKey()
        ]);

        $this->assertDatabaseHas(self::TABLE_APPOINTMENTS, $checkingData);
    }

    public function testUpdatePatientStatusFromDischarged()
    {
        Event::fake();

        $activeStatusId = PatientStatus::getActiveId();
        $dischargedStatusId = PatientStatus::getDischargedId();
        $dischargedDocumentTypeIds = PatientDocumentType::getFileTypeIDsLikeDischarge();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $patient = Patient::find(self::ID_FOR_TEST);

        $patient->patient_id = $appointmentData['patient_id'];
        $patient->status_id = $dischargedStatusId;
        $patient->save();
        $patient->documents()->create([
            'document_type_id' => $dischargedDocumentTypeIds[0],
            'created_at' => Carbon::createFromFormat('M d, Y h:i A', $appointmentData['date_created'])->subDay()
        ]);

        $this->assertDatabaseHas(self::TABLE_PATIENTS, [
            'patient_id' => $appointmentData['patient_id'],
            'status_id' => $dischargedStatusId
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENTS, [
            'patient_id' => $appointmentData['patient_id'],
            'status_id' => $activeStatusId
        ]);
    }

    public function testUpdatePatientStatusFromArchived()
    {
        Event::fake();

        $activeStatusId = PatientStatus::getActiveId();
        $archivedStatusId = PatientStatus::getArchivedId();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        $patient = Patient::find(self::ID_FOR_TEST);

        $patient->patient_id = $appointmentData['patient_id'];
        $patient->status_id = $archivedStatusId;
        $patient->save();

        $this->assertDatabaseHas(self::TABLE_PATIENTS, [
            'patient_id' => $appointmentData['patient_id'],
            'status_id' => $archivedStatusId
        ]);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENTS, [
            'patient_id' => $appointmentData['patient_id'],
            'status_id' => $activeStatusId
        ]);
    }
    
    public function testPatientHasProviderRelationshipCreation()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Carbon::setTestNow(Carbon::parse($appointmentData['date_created']));

        $provider = Provider::find(self::ID_FOR_TEST);
        $patient = Patient::find(self::ID_FOR_TEST);

        $provider->provider_name = $appointmentData['provider_name'];
        $provider->save();
        $patient->patient_id = $appointmentData['patient_id'];
        $patient->save();

        $checkingData = ['patients_id' => $patient->id, 'providers_id' => $provider->id];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_HAS_PROVIDERS, $checkingData);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_HAS_PROVIDERS, $checkingData);
    }
    
    public function testSystemCommentCreationOnPatientHasProviderCreation()
    {
        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Carbon::setTestNow(Carbon::parse($appointmentData['date_created']));

        $patient = Patient::find(self::ID_FOR_TEST);
        $patient->patient_id = $appointmentData['patient_id'];
        $patient->save();

        $comment = trans('comments.provider_assigned_automatically', [
            'provider_name' => $appointmentData['provider_name'],
        ]);

        $checkingData = ['patient_id' => $patient->id, 'comment' => $comment, 'is_system_comment' => 1];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_COMMENTS, $checkingData);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_COMMENTS, $checkingData);
    }

    public function testPatientHasProviderUpdateChartReadOnly()
    {
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Carbon::setTestNow(Carbon::parse($appointmentData['date_created']));

        $provider = Provider::find(self::ID_FOR_TEST);
        $patient = Patient::find(self::ID_FOR_TEST);

        $provider->provider_name = $appointmentData['provider_name'];
        $provider->save();
        $patient->patient_id = $appointmentData['patient_id'];
        $patient->save();

        $checkingData = [
            'patients_id' => $patient->id,
            'providers_id' => $provider->id,
            'chart_read_only' => 1
        ];

        PatientHasProvider::create($checkingData);

        $this->assertDatabaseHas(self::TABLE_PATIENT_HAS_PROVIDERS, $checkingData);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $checkingData['chart_read_only'] = 0;

        $this->assertDatabaseHas(self::TABLE_PATIENT_HAS_PROVIDERS, $checkingData);
    }

    public function testSystemCommentCreationOnPatientHasProviderUpdate()
    {
        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Carbon::setTestNow(Carbon::parse($appointmentData['date_created']));

        $provider = Provider::find(self::ID_FOR_TEST);
        $patient = Patient::find(self::ID_FOR_TEST);

        $provider->provider_name = $appointmentData['provider_name'];
        $provider->save();
        $patient->patient_id = $appointmentData['patient_id'];
        $patient->save();

        PatientHasProvider::create([
            'patients_id' => $patient->id,
            'providers_id' => $provider->id,
            'chart_read_only' => 1
        ]);

        $comment = trans('comments.provider_assigned_automatically', [
            'provider_name' => $provider->provider_name,
        ]);

        $checkingData = ['patient_id' => $patient->id, 'comment' => $comment, 'is_system_comment' => 1];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_COMMENTS, $checkingData);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_COMMENTS, $checkingData);
    }
    
    public function testSendSmsToProviderIfFirstAppointmentWithPhoneAndFutureTime()
    {
        Bus::fake();
        Event::fake();

        $appointmentData = AppointmentsOfficeAllyHelper::getAppointmentDataForHtml();
        $officeData = AppointmentsOfficeAllyHelper::getOfficeDataForHtml();

        Carbon::setTestNow(Carbon::parse($appointmentData['date_created'])->subDay());

        $appointment = Appointment::find(self::ID_FOR_TEST);
        $appointment->idAppointments = $appointmentData['idAppointments'];
        $appointment->save();
        $provider = Provider::find(self::ID_FOR_TEST);
        $provider->provider_name = $appointmentData['provider_name'];
        $provider->save();

        $checkingData = [
            'appointment_id' => $appointment->id,
            'provider_id' => $provider->id,
            'type' => AppointmentNotification::TYPE_NEW_PATIENT,
            'status' => AppointmentNotification::STATUS_NEW
        ];

        $this->assertDatabaseMissing(self::TABLE_APPOINTMENT_NOTIFICATIONS, $checkingData);

        $appointmentsHtml = AppointmentsOfficeAllyHelper::getMockAppointmentsHtml($appointmentData, $officeData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getAppointments', $appointmentsHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new AppointmentsParser(null, null))->handleParser();

        Bus::assertDispatched(RingcentralSms::class);

        $this->assertDatabaseHas(self::TABLE_APPOINTMENT_NOTIFICATIONS, $checkingData);
    }
}
