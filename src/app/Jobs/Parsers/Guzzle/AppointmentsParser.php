<?php

namespace App\Jobs\Parsers\Guzzle;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Events\NeedsWriteSystemComment;
use App\Helpers\RetryJobQueueHelper;
use App\Jobs\DeleteAppointments;
use App\Models\PatientHasProvider;
use App\Option;
use App\Appointment;
use App\DTO\OfficeAlly\AppointmentParser\AppointmentsParserAppointmentDTO;
use App\DTO\OfficeAlly\AppointmentParser\AppointmentsParserPatientDTO;
use App\Office;
use App\OfficeRoom;
use App\Patient;
use App\PatientStatus;
use App\Provider;
use App\Status;
use App\Traits\Appointments\SendProviderNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\ExceptionNotificator;
use App\Models\TreatmentModality;
use App\Notifications\AnErrorOccurred;
use App\Traits\Parsers\OfficeAlly\TableProcessing;

/**
 * Copied from \App\Jobs\Parsers\Puppeteer\AppointmentParser
 * Class AppointmentsParser
 * @package App\Jobs\Parsers\Guzzle
 */
class AppointmentsParser extends AbstractParser
{
    use SendProviderNotification, TableProcessing;

    /** @var int|null */
    private $pastDays;
    
    /** @var int|null */
    private $upcomingDays;
    
    private $parsedAt;
    
    /** @var int */
    private $parsedAppointmentsCount = 0;
    
    /** @var int */
    private $visitCreatedId;

    /** @var array */
    private $cancelStatuses = [];
    
    /** @var int */
    private $archivedStatusId;
    
    /** @var int */
    private $dischargedStatusId;
    
    /** @var bool */
    private $officesParsed = false;

    private $columnsMappingTemplate = [
        'time' => ['index' => null, 'name' => 'Time', 'required' => false],
        'patient_name' => ['index' => null, 'name' => 'Patient Name', 'required' => false],
        'address' => ['index' => null, 'name' => 'Address', 'required' => true],
        'appointment_id' => ['index' => null, 'name' => 'Appointment ID', 'required' => true],
        'age' => ['index' => null, 'name' => 'Age', 'required' => false],
        // 'auth_number' => ['index' => null, 'name' => 'Auth. Number', 'required' => true],
        'cell_phone' => ['index' => null, 'name' => 'Cell Phone', 'required' => true],
        'check_in' => ['index' => null, 'name' => 'Check In?', 'required' => true],
        'date_created' => ['index' => null, 'name' => 'Date Created', 'required' => true],
        'birth_date' => ['index' => null, 'name' => 'DOB', 'required' => false],
        'elig_copay' => ['index' => null, 'name' => 'Eligibility Copay', 'required' => false],
        'elig_status' => ['index' => null, 'name' => 'Automated Eligibility Status', 'required' => true],
        'first_name' => ['index' => null, 'name' => 'First Name', 'required' => true],
        'home_phone' => ['index' => null, 'name' => 'Home Phone', 'required' => true],
        'insured_name' => ['index' => null, 'name' => 'Insured Name', 'required' => true],
        'last_name' => ['index' => null, 'name' => 'Last Name', 'required' => true],
        'middle_initial' => ['index' => null, 'name' => 'Middle Initial', 'required' => true],
        'visits_auth' => ['index' => null, 'name' => 'No. of Visits Auth.', 'required' => true],
        'notes' => ['index' => null, 'name' => 'Notes', 'required' => true],
        'office' => ['index' => null, 'name' => 'Office', 'required' => true],
        'patient_account_number' => ['index' => null, 'name' => 'Patient Account No', 'required' => true],
        'patient_id' => ['index' => null, 'name' => 'Patient ID', 'required' => true],
        'primary_insurance' => ['index' => null, 'name' => 'Primary Insurance', 'required' => true],
        'provider_name' => ['index' => null, 'name' => 'Provider Name', 'required' => true],
        'reason_for_visit' => ['index' => null, 'name' => 'Reason For Visit', 'required' => true],
        'reffering_provider' => ['index' => null, 'name' => 'Referring Provider', 'required' => true],
        'reminder_status' => ['index' => null, 'name' => 'Reminder Status', 'required' => false],
        'resource' => ['index' => null, 'name' => 'Resource', 'required' => false],
        'sheldued_by' => ['index' => null, 'name' => 'Scheduled By', 'required' => true],
        'secondary_insured_name' => ['index' => null, 'name' => 'Sec. Insured Name', 'required' => true],
        'secondary_insurance' => ['index' => null, 'name' => 'Secondary Insurance', 'required' => true],
        'sex' => ['index' => null, 'name' => 'Sex', 'required' => true],
        'status' => ['index' => null, 'name' => 'Status', 'required' => true],
        'visit_copay' => ['index' => null, 'name' => 'Visit Copay', 'required' => true],
        'visit_length' => ['index' => null, 'name' => 'Visit Length', 'required' => true],
        'work_phone' => ['index' => null, 'name' => 'Work Phone', 'required' => true],
        'add_button' => ['index' => null, 'name' => 'Add', 'required' => true],
    ];
    
    /**
     * Create a new job instance.
     *
     * @param null $pastDays
     * @param null $upcomingDays
     */
    public function __construct($pastDays = null, $upcomingDays = null)
    {
        $this->pastDays = $pastDays;
        $this->upcomingDays = $upcomingDays;
        parent::__construct();
    }

    public function handleParser()
    {
        $this->archivedStatusId = PatientStatus::getArchivedId();
        $this->dischargedStatusId = PatientStatus::getDischargedId();
        $this->visitCreatedId = Status::getVisitCreatedId();
        $this->cancelStatuses = Status::getOtherCancelStatusesId();
        $this->parsedAt = Carbon::now()->timestamp;

        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_3);
        $period = $this->getPeriod();
        $offices = Office::query()
            ->whereNotNull('external_id')
            ->get();
        
        $pagesCount = 0;
        foreach ($offices as $office) {
            foreach ($period as $date) {
                $appointmentsPage = $officeAllyHelper->getAppointments($date, $office->external_id);

                $hasData = $this->appointmentsCrawler($appointmentsPage);
                if ($hasData) {
                    $pagesCount++;
                }
            }
        }

        $neededPageCount = $period->first()->diffInDays($period->last()) * $offices->count() + $offices->count();

        echo "[" . Carbon::now()->toDateTimeString() . "] Counter: {$this->parsedAppointmentsCount} | Page Count: $pagesCount | Needed Page Count: "
            . $neededPageCount . ' | Parsed At: ' . $this->parsedAt . PHP_EOL;

        if ($this->parsedAppointmentsCount >= 0 && $pagesCount >= $neededPageCount) {
            echo 'Start Deleting Appointments' . PHP_EOL;
            \Bus::dispatchNow(new DeleteAppointments($this->parsedAt, $this->pastDays, $this->upcomingDays));
        }
    }
    
    /**
     * @param $appointmentsPage
     */
    private function appointmentsCrawler($appointmentsPage)
    {
        $crawler = new Crawler($appointmentsPage);
        
        $this->parseOffices($crawler);

        $tableHeaders = $crawler->filter('#divDaily .tblAppts thead > tr > th');
        $columnsMapping = $this->getColumnsMappingWithIndexes($this->columnsMappingTemplate, $tableHeaders);
        $missedColumns = $this->getMissedRequiredColumns($columnsMapping);

        if (count($missedColumns)) {
            $columns = array_column(array_values($missedColumns), 'name');
            $message = '[AppointmentsParser] The following required columns are missed: ' . implode(', ', $columns);
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($message), ['office_ally' => 'emergency']);
            
            return false;
        }

        $hasData = false;
        $crawler->filter('#divDaily .tblAppts > tr')->each(function ($node, $i) use (&$hasData, &$columnsMapping) {
            $hasData = true;
            
            $appointmentId = $this->getIntVal('appointment_id', $node, $columnsMapping);
            if (empty($appointmentId)) {
                return;
            }

            $this->parsedAppointmentsCount++;

            $status = Status::firstOrCreate(['status' => $this->getStringVal('status', $node, $columnsMapping)]);
            $office = Office::firstOrCreate(['office' => $this->getStringVal('office', $node, $columnsMapping)]);

            $patientData = new AppointmentsParserPatientDTO([
                'patient_id' => $this->getIntVal('patient_id', $node, $columnsMapping),
                'patient_account_number' => $this->getStringVal('patient_account_number', $node, $columnsMapping),
                'first_name' => $this->getStringVal('first_name', $node, $columnsMapping),
                'last_name' => $this->getStringVal('last_name', $node, $columnsMapping),
                'middle_initial' => $this->getStringVal('middle_initial', $node, $columnsMapping),
                'insured_name' => $this->getStringVal('insured_name', $node, $columnsMapping),
                'secondary_insured_name' => $this->getStringVal('secondary_insured_name', $node, $columnsMapping),
                'address' => $this->getStringVal('address', $node, $columnsMapping),
                'cell_phone' => $this->getStringVal('cell_phone', $node, $columnsMapping),
                'home_phone' => $this->getStringVal('home_phone', $node, $columnsMapping),
                'work_phone' => $this->getStringVal('work_phone', $node, $columnsMapping),
                'visits_auth' => $this->getIntVal('visits_auth', $node, $columnsMapping),
                'primary_insurance' => $this->getStringVal('primary_insurance', $node, $columnsMapping),
                'secondary_insurance' => $this->getStringVal('secondary_insurance', $node, $columnsMapping),
                'sex' => $this->getStringVal('sex', $node, $columnsMapping),
                'elig_copay' => $this->getStringVal('elig_copay', $node, $columnsMapping),
                'elig_status' => $this->getStringVal('elig_status', $node, $columnsMapping),
                'reffering_provider' => $this->getStringVal('reffering_provider', $node, $columnsMapping),
                'visit_copay' => $this->getFloatVal('visit_copay', $node, $columnsMapping),
            ]);
            
            $patient = Patient::firstOrCreate(['patient_id' => $patientData->patient_id], $patientData->toArray());
            
            $appointmentDto = new AppointmentsParserAppointmentDTO([
                'time'             => isset($columnsMapping['add_button']) && isset($columnsMapping['add_button']['index']) ? strtotime($node->children()->eq($columnsMapping['add_button']['index'])->children()->attr('title')) : null,
                'idAppointments'   => $appointmentId,
                'resource'         => $this->getStringVal('resource', $node, $columnsMapping),
                'visit_copay'      => $this->getFloatVal('visit_copay', $node, $columnsMapping),
                'visit_length'     => $this->getIntVal('visit_length', $node, $columnsMapping),
                'notes'            => $this->getStringVal('notes', $node, $columnsMapping),
                'reason_for_visit' => $this->getStringVal('reason_for_visit', $node, $columnsMapping),
                'sheldued_by'      => $this->getStringVal('sheldued_by', $node, $columnsMapping),
                'date_created'     => $this->getStringVal('date_created', $node, $columnsMapping),
                'check_in'         => $this->getStringVal('check_in', $node, $columnsMapping),
                'not_found_count'  => 0,
                'parsed_at'        => $this->parsedAt,
            ]);

            $appointmentData = $appointmentDto->toArray();

            $treatmentModalityId = $this->getTreatmentModalityIdByName($appointmentData['reason_for_visit']);

            $appointmentData['treatment_modality_id'] = $treatmentModalityId;
            $appointmentData['reason_for_visit'] = $appointmentData['reason_for_visit'];
            $appointmentData['is_initial'] = in_array($treatmentModalityId, TreatmentModality::initialEvaluationIds());
            
            $officeRoomName = preg_replace('/\d ?- ?/', '', $appointmentData['resource']);
            $officeRoom = null;
            if (!empty($officeRoomName)) {
                $officeRoom = OfficeRoom::firstOrCreate(['name' => $officeRoomName, 'office_id' => $office->id]);
            }
            $provider = Provider::withTrashed()->firstOrCreate(['provider_name' => $this->getStringVal('provider_name', $node, $columnsMapping)]);
            $appointmentData = array_merge($appointmentData, [
                'providers_id'            => $provider->getKey(),
                'patients_id'             => $patient->getKey(),
                'offices_id'              => $office->getKey(),
                'office_room_id'          => optional($officeRoom)->getKey(),
                'appointment_statuses_id' => $status->getKey(),
            ]);

            $appointment = Appointment::where('idAppointments', $appointmentData['idAppointments'])
                ->withTrashed()
                ->first();
            if ($appointment && $appointment->trashed()) {
                $appointment->restore();
            }
            if (!$appointment) {
                $appointment = Appointment::create($appointmentData);
            } else if (!RetryJobQueueHelper::checkAppointmentJobs($appointment->id)) {
                $appointment->update($appointmentData);
            }

            $this->attachPatientToProvider($appointment, $patient, $provider, $office);
        });
        
        return $hasData;
    }

    private function parseOffices(Crawler $crawler)
    {
        if ($this->officesParsed) {
            return;
        }

        $crawler->filter('select#ctl00_phFolderContent_Appointments_lstOffice option')->each(function ($node) {
            $officeId = $node->attr('value');
            $officeName = $node->text();
            if (!empty($officeId) && !empty($officeName)) {
                Office::query()->updateOrCreate([
                    'external_id' => $officeId,
                ], [
                    'office'      => $officeName,
                ]);
            }
            $this->officesParsed = true;
        });
    }

    private function attachPatientToProvider($appointment, $patient, $provider, $office)
    {
        $isFirstPatientAppointmentWithProvider = false;
        $appointmentTime = Carbon::createFromTimestamp($appointment->time);
        $comment = trans('comments.provider_assigned_automatically', [
            'provider_name' => $provider->provider_name,
        ]);

        if ($patient->allProviders()->withTrashed()->where('id', $provider->id)->count() === 0) {
            if ($appointmentTime->gte(Carbon::now()->subDay()) && !in_array($appointment->appointment_statuses_id, $this->cancelStatuses)) {
                $isFirstPatientAppointmentWithProvider = true;

                PatientHasProvider::create([
                    'patients_id' => $patient->id,
                    'providers_id' => $provider->id
                ]);

                event(new NeedsWriteSystemComment($patient->id, $comment));
            }
        } else {
            $patientHasProvider = PatientHasProvider::where('providers_id', $provider->id)
                ->where('patients_id', $patient->id)
                ->first();
            $count = $patientHasProvider->update(['chart_read_only' => false]);
            
            if ($count > 0) {
                $isFirstPatientAppointmentWithProvider = true;
                event(new NeedsWriteSystemComment($patient->id, $comment));
            }
        }

        if ($isFirstPatientAppointmentWithProvider && $appointmentTime->isFuture()) {
            $this->sendFirstAppointmentNotifications($appointment, $provider, $patient, $office);
        }
    }

    /**
     * @return CarbonPeriod
     */
    private function getPeriod()
    {
        if ($this->pastDays > 0) {
            $date = Carbon::now()->subDays($this->pastDays);
        } else {
            $date = Carbon::now()->subDays(config('parser.parsing_depth'));
        }
        
        if ($this->upcomingDays > 0) {
            $maxDate = Carbon::now()->addDays($this->upcomingDays);
        } else {
            $maxDate = Carbon::now()->addDays(config('parser.parsing_depth_after_today'));
        }
        
        return CarbonPeriod::create($date, $maxDate);
    }

    private function getTreatmentModalityIdByName(string $reasonForVisit): int
    {
        $treatmentModalityId = TreatmentModality::getTreatmentModalityIdByName($reasonForVisit);

        if (empty($treatmentModalityId)) {
            $treatmentModalityId = $reasonForVisit === Appointment::REASON_TELEHEALTH ?
                TreatmentModality::getTreatmentModalityIdByName(TreatmentModality::DEFAULT_TELEHEALTH_TREATMENT_MODALITY) :
                TreatmentModality::getTreatmentModalityIdByName(TreatmentModality::DEFAULT_IN_PERSON_TREATMENT_MODALITY);
        }

        return $treatmentModalityId;
    }
}
