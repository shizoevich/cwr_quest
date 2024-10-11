<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\PatientDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\KaiserAppointment;
use App\Option;
use App\Patient;
use App\PatientStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\ExceptionNotificator;
use App\Helpers\RetryJobQueueHelper;
use App\Notifications\AnErrorOccurred;

/**
 * Class PatientsParser
 * @package App\Jobs\Parsers\Guzzle
 */
class PatientsParser extends AbstractParser
{
    private $queueName;
    private $withProfileParser;
    private $needsParseProfileIds = [];
    private $newStatusId;
    private $activeStatusId;
    private $inactiveStatusId;


    /**
     * Create a new job instance.
     *
     * @param string $queue
     * @param bool   $withProfileParser
     */
    public function __construct($queue = 'parser', $withProfileParser = true)
    {
        $this->queueName = $queue;
        $this->withProfileParser = $withProfileParser;

        $this->newStatusId = PatientStatus::getNewId();
        $this->activeStatusId = PatientStatus::getActiveId();
        $this->inactiveStatusId = PatientStatus::getInactiveId();

        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_2);
        $patients = $officeAllyHelper->getPatientList();
        if ($patients === null) {
            return;
        }

        $patientIds = $this->patientsCrawler($patients);
        $patientIds = array_unique($patientIds);

        if (count($patients) && !count($patientIds)) {
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred(sprintf('Parsed %d patients, but saved %d', count($patients), count($patientIds))));
        }

        $this->needsParseProfileIds = array_unique($this->needsParseProfileIds);
        if (count($this->needsParseProfileIds) && $this->withProfileParser) {
            $job = (new PatientProfilesParser($this->needsParseProfileIds))->onQueue($this->queueName);
            \Bus::dispatchNow($job);
        }

        Artisan::call('patients:update-statuses', ['--sync' => true]);
    }

    /**
     * @param array $patients
     */
    private function patientsCrawler(array &$patients)
    {
        $patientIds = [];

        foreach ($patients as $item) {
            $dateOfBirth = $item['cell'][7];

            $patientDTO = new PatientDTO([
                'patient_id'     => $item['cell'][0],
                'last_name'      => $item['cell'][1],
                'first_name'     => $item['cell'][2],
                'middle_initial' => $item['cell'][6],
                'date_of_birth'  => empty($dateOfBirth) ? null : Carbon::parse($dateOfBirth)->toDateString(),
            ]);

            if (!$patientDTO->patient_id) {
                continue;
            }

            $patient = Patient::where('patient_id', $patientDTO->patient_id)->first();

            if (!$patient) {
                $patient = Patient::create($patientDTO->toArray());
            } else if (!RetryJobQueueHelper::checkPatientJobs($patient->id)) {
                $patient->update($patientDTO->toArray());
            }

            if (!$patient->status_id) {
                $patient->update(['status_id' => $this->newStatusId]);
            }

            $patientIds[] = $patientDTO->toArray()['patient_id'];

            $patientCreatedAt = Carbon::parse($patient->created_at);
            $insuranceVerificationRequired = optional($patient->insurancePlan)->is_verification_required && in_array($patient->status_id, [$this->newStatusId, $this->activeStatusId, $this->inactiveStatusId]);
            if (!$patient->first_name || !$patient->created_patient_date || $insuranceVerificationRequired || $patientCreatedAt->gte(Carbon::now()->subHours(5))) {
                $this->needsParseProfileIds[] = $patient->patient_id;
            }

            // @todo check if logic is still valid
            if ($patient->wasRecentlyCreated && $patient->getKey() !== null) {
                KaiserAppointment::query()
                    ->where('first_name', $patient->first_name)
                    ->where('last_name', $patient->last_name)
                    ->where('date_of_birth', Carbon::parse($patient->date_of_birth)->toDateString())
                    ->update(['patient_id' => $patient->getKey()]);
            }
        }

        return $patientIds;
    }
}
