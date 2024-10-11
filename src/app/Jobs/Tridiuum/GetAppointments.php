<?php

namespace App\Jobs\Tridiuum;

use App\Enums\VisitType;
use App\Helpers\TridiuumHelper;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Models\Patient\Inquiry\PatientInquiryRegistrationMethod;
use App\Models\Patient\Inquiry\PatientInquirySource;
use App\Models\Patient\Inquiry\PatientInquiryStage;
use App\Office;
use App\Option;
use App\Patient;
use App\PatientStatus;
use App\Provider;
use App\Appointment;
use App\KaiserAppointment;
use App\TridiuumSite;
use App\Models\Language;
use App\Repositories\Appointment\Model\AppointmentRepositoryInterface;
use App\Repositories\NewPatientsCRM\PatientInquiry\PatientInquiryRepositoryInterface;
use App\Traits\LogDataTrait;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException; 
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GetAppointments extends AbstractParser
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogDataTrait;

    const TEST_PATIENTS_MRN = [
        '5554',
        '76534245',
        '456245',
        '3562454',
        '123459678',
        'x',
    ];

    /** @var null */
    protected $userId;

    protected $from;

    protected $to;

    protected $languages = [];

    /**
     * Create a new job instance.
     *
     * @param int|array|null $userId
     */
    public function __construct($userId = null, Carbon $from = null, Carbon $to = null)
    {
        $this->connection = 'redis_long';
        $this->queue = 'tridiuum';

        $this->userId = $userId;
        $this->from = $from ?? Carbon::now()->subDays(20)->startOfDay();
        $this->to = $to ?? Carbon::yesterday()->addMonth()->endOfDay();

        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $this->languages = Language::query()
            ->whereNotNull('officeally_id')
            ->pluck('id', 'officeally_id')
            ->toArray();

        $this->processParser();
        $this->resetParserStatus();
    }

    private function processParser(): void
    {
        if (!config('parser.tridiuum.enabled')) {
            return;
        }

        $offices = Office::query()
            ->where('tridiuum_is_enabled', 1)
            ->pluck('id', 'tridiuum_site_id')
            ->toArray();
        $sites = TridiuumSite::all()->toArray();
        $sitesList = [];
        foreach ($sites as $site) {
            $sitesList[$site['tridiuum_site_id']] = [
                'id' => $site['id'],
                'name' => $site['tridiuum_site_name']
            ];
        }

        $tridiuumHelper = new TridiuumHelper();
        Provider::query()
            ->select('providers.*')
            ->where('tridiuum_sync_appointments', 1)
            ->when($this->userId, function (Builder $query, $userId) {
                $query->join('users', 'users.provider_id', 'providers.id')->where('users.id', $userId);
            })
            ->with('tridiuumProvider')
            ->whereHas('tridiuumProvider')
            ->each(function (Provider $provider) use ($offices, $sitesList, $tridiuumHelper) {
                try {
                    $this->logAdditionalData('Get appointments for provider: ' . $provider->id);

                    $tridiuumAppointments = $tridiuumHelper->getAppointments(
                        $this->from,
                        $this->to,
                        $provider->tridiuumProvider->external_id
                    );

                    $this->logAdditionalData('Successfully got appointments from tridiuum');

                    if (!$tridiuumAppointments) {
                        return;
                    }

                    foreach ($tridiuumAppointments as $tridiuumAppointment) {
                        $firstName = sanitize_name(data_get($tridiuumAppointment, 'patient.first_name'));
                        $lastName = sanitize_name(data_get($tridiuumAppointment, 'patient.last_name'));
                        if (!$firstName || !$lastName) {
                            $this->logAdditionalData('Appointment without first_name, last_name:');
                            $this->logAdditionalData($tridiuumAppointment);
                            continue;
                        }
                        if (data_get($tridiuumAppointment, 'record_type') == 'hold') {
                            $this->logAdditionalData('Appointment record_type equals to "hold":');
                            $this->logAdditionalData($tridiuumAppointment);
                            continue;
                        }
                        if (empty(data_get($tridiuumAppointment, 'patient.mrn'))) {
                            $this->logAdditionalData('Appointment for patient without mrn:');
                            $this->logAdditionalData($tridiuumAppointment);
                            continue;
                        }

                        //@todo: delete after implementing mrn fix in TridiuumHelper@getFromObjectPatientValue
                        if (in_array(data_get($tridiuumAppointment, 'patient.mrn'), self::TEST_PATIENTS_MRN)) {
                            $this->logAdditionalData('Appointment for test patient: ' . data_get($tridiuumAppointment, 'patient.mrn'));
                            continue;
                        }

                        $patient = Patient::query()->where([
                            'first_name'    => $firstName,
                            'last_name'     => $lastName,
                            'date_of_birth' => Carbon::parse(data_get($tridiuumAppointment, 'patient.dob'))->toDateString()
                        ])->first();

                        $patientData = $tridiuumHelper->getPatientInfo(data_get($tridiuumAppointment, 'patient.mrn'));
                        $this->logAdditionalData('Successfully got patient info: ' . data_get($tridiuumAppointment, 'patient.mrn'));

                        /**
                         * Create patient in OA and sync with EHR
                         */
                        if (!$patient) {
                            $patientId = $this->createPatientInOfficeAlly($patientData);
                            $this->logAdditionalData('Successfully created patient in OA: ' . $patientId);

                            if ($patientId) {
                                $patient = $this->createPatient($patientId, $patientData);
                            }
                        }
                        if ($patient) {
                            $startDate = Carbon::parse(data_get($tridiuumAppointment, 'start_date'));
                            $hasAppointment = $patient->appointments()
                                ->withTrashed()
                                ->where('time', '>=', $startDate->copy()->startOfDay()->timestamp)
                                ->where('time', '<=', $startDate->copy()->endOfDay()->timestamp)
                                ->where(function($query) {
                                    $query->whereNull('deleted_at')
                                        ->orWhere('is_created_by_tridiuum', true);
                                })
                                ->exists();
                            if ($hasAppointment) {
                                $this->logAdditionalData('Appointment already exists:');
                                $this->logAdditionalData($tridiuumAppointment);
                                continue;
                            }
                        }

                        $tridiuumSiteId = data_get($tridiuumAppointment, 'site.id');
                        $tridiuumSiteName = data_get($tridiuumAppointment, 'site.name');

                        if (array_key_exists($tridiuumSiteId, $sitesList)) {
                            if ($tridiuumSiteName != $sitesList[$tridiuumSiteId]['name']) {
                                TridiuumSite::where('tridiuum_site_id', $tridiuumSiteId)
                                    ->first()
                                    ->update(['tridiuum_site_name' => $tridiuumSiteName]);
                                $sitesList[$tridiuumSiteId]['name'] = $tridiuumSiteName;
                            }
                        } else {
                            $newSite = TridiuumSite::create([
                                'tridiuum_site_id'   => $tridiuumSiteId,
                                'tridiuum_site_name' => $tridiuumSiteName
                            ]);
                            $sitesList[$tridiuumSiteId] = [
                                'id'   => $newSite->getKey(),
                                'name' => $newSite->tridiuum_site_name
                            ];
                        }
                        
                        //
                        $phones = [
                            'phone_number'             => data_get($patientData, 'phone_number'),
                            'alternate_phone_number'   => data_get($patientData, 'alternate_phone_number'),
                            'alternate_phone_number_2' => data_get($patientData, 'alternate_phone_number_2')
                        ];
                        $phones = $this->parsePhoneNumbers($phones, true);

                        $siteId = $sitesList[$tridiuumSiteId]['id'];
                        
                        /** @var KaiserAppointment $kaiserAppointment */
                        $kaiserAppointment = $provider->kaiserAppointments()->updateOrCreate([
                            'tridiuum_id' => data_get($tridiuumAppointment, 'id'),
                        ], [
                            'tridiuum_id'   => data_get($tridiuumAppointment, 'id'),
                            'start_date'    => Carbon::parse(data_get($tridiuumAppointment, 'start_date')),
                            'duration'      => (int)data_get($tridiuumAppointment, 'duration_value'),
                            'notes'         => data_get($tridiuumAppointment, 'notes'),
                            'reason'        => data_get($tridiuumAppointment, 'reason'),
                            'patient_id'    => optional($patient)->getKey(),
                            'mrn'           => data_get($tridiuumAppointment, 'patient.mrn'),
                            'first_name'    => sanitize_name(data_get($tridiuumAppointment, 'patient.first_name')),
                            'last_name'     => sanitize_name(data_get($tridiuumAppointment, 'patient.last_name')),
                            'sex'           => data_get($patientData, 'sex'),
                            'cell_phone'    => data_get($phones, 'cell_phone'),
                            'date_of_birth' => Carbon::parse(data_get($tridiuumAppointment, 'patient.dob')),
                            'site_id'       => $siteId,
                            'is_virtual'    => (bool)data_get($tridiuumAppointment, 'is_virtual'),
                        ]);
                        
                        if (!$kaiserAppointment->internal_id && $kaiserAppointment->patient && data_get($offices, $tridiuumSiteId) && $startDate->copy()->startOfDay()->gte(Carbon::today())) {
                            $startDate = Carbon::parse($kaiserAppointment->start_date);
                            $appointmentRepository = app()->make(AppointmentRepositoryInterface::class);
                            $createdAppointment = $appointmentRepository->createAppointment([
                                'patient_id' => $kaiserAppointment->patient_id,
                                'office_id' => data_get($offices, $tridiuumSiteId),
                                'provider_id' => $kaiserAppointment->provider_id,
                                'visit_type' => $kaiserAppointment->is_virtual ? VisitType::VIRTUAL : VisitType::IN_PERSON,
                                'reason_for_visit' => $kaiserAppointment->getTreatmentModalityId(),
                                'date' => $startDate->format('Y-m-d'),
                                'time' => $startDate->format('H:i'),
                                'visit_length' => $kaiserAppointment->duration,
                            ]);
                            optional($createdAppointment)->update(['is_created_by_tridiuum' => true]);
                            
                            $kaiserAppointment->update([
                                'internal_id' => optional($createdAppointment)->id
                            ]);

                            $this->logAdditionalData('Successfully created appointment: ' . $createdAppointment->id);

                            if ($createdAppointment && $patient->activeInquiry()->doesntExist()) {
                                $lastVisitCreatedAppointment = Appointment::query()
                                    ->select(['id', 'time'])
                                    ->where('patients_id', $patient->id)
                                    ->onlyVisitCreated()
                                    ->orderBy('time', 'desc')
                                    ->first();

                                if (empty($lastVisitCreatedAppointment) ||
                                    Carbon::now()->diff(Carbon::createFromTimestamp($lastVisitCreatedAppointment->time))->days > PatientInquiry::DAYS_FOR_NEW_EPISODE
                                ) {
                                    app()->make(PatientInquiryRepositoryInterface::class)->create(
                                        [
                                            'inquirable_id' => $kaiserAppointment->patient_id,
                                            'inquirable_classname' => class_basename(Patient::class),
                                            'registration_method_id' => PatientInquiryRegistrationMethod::getFaxId(),
                                            'source_id' => PatientInquirySource::getInsuranceCompId(),
                                            'marketing_activity' => PatientInquiry::MARKETING_ACTIVITY_FOR_TRIDIUUM_PATIENTS,
                                        ],
                                        PatientInquiryStage::getInProgressId(),
                                        !empty($lastVisitCreatedAppointment),
                                        true
                                    );
                                }
                            }
                        }
                    }
                } catch (RequestException $exception) {
                    Log::error($exception);
                    \App\Helpers\SentryLogger::tridiuumCaptureException($exception);
                } catch (\Exception $exception) {
                    Log::error($exception);
                }
            });

        if (config('officeally.env') !== 'production') {
            return;
        }

        try {
            $this->logAdditionalData('Start creating patients in OA');

            $this->createPatients();

            $this->logAdditionalData('Successfully created patients in OA');
        } catch (RequestException $exception) {
            Log::error($exception);
            \App\Helpers\SentryLogger::tridiuumCaptureException($exception);
        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }

    public function failed(\Exception $exception): void
    {
        $this->resetParserStatus();
    }

    private function resetParserStatus(): void
    {
        Option::setOptionValue('is_restarting_tridiuum_parsers', 0);
    }

    private function logAdditionalData($data, string $type = 'INFO'): void
    {
        if (! config('parser.tridiuum.get_appointments.additional_logging_enabled')) {
            return;
        }

        $this->logData(config('parser.tridiuum.get_appointments.log_file_storage_path'), $data, $type);
    }

    private function createPatients()
    {
        $patients = [];
        $tridiuumHelper = new TridiuumHelper();
        KaiserAppointment::query()
            ->whereNull('patient_id')
            ->whereNotNull('mrn')
            ->chunk(100, function (Collection $results) use (&$patients, $tridiuumHelper) {
                foreach ($results->groupBy('provider_id') as $providerId => $providerResults) {
                    foreach ($providerResults as $kaiserAppointment) {
                        $patientData = $tridiuumHelper->getPatientInfo($kaiserAppointment->mrn);
                        $patientId = $this->createPatientInOfficeAlly($patientData);
                        if ($patientId) {
                            $patient = $this->createPatient($patientId, $patientData);
                            $patients[] = $patient->id;
                        }
                    }
                }
            });
        
        return array_unique($patients);
    }

    public function createPatient($patientId, $patientData)
    {
        $phones = [
            'phone_number' => data_get($patientData, 'phone_number'),
            'alternate_phone_number' => data_get($patientData, 'alternate_phone_number'),
            'alternate_phone_number_2' => data_get($patientData, 'alternate_phone_number_2')
        ];
        $phones = $this->parsePhoneNumbers($phones, true);

        $sex = data_get($patientData, 'sex');
        $externalLanguageId = data_get($patientData, 'preferred_language_id');
        
        return Patient::firstOrCreate([
            'patient_id' => $patientId,
        ], array_merge([
            'first_name' => sanitize_name(data_get($patientData, 'first_name')),
            'last_name' => sanitize_name(data_get($patientData, 'last_name')),
            'middle_initial' => title_case(data_get($patientData, 'middle_initial')),
            'email' => data_get($patientData, 'email'),
            'sex' => isset($sex) ? strtoupper($sex) : null,
            'date_of_birth' => Carbon::parse(data_get($patientData, 'dob')),
            'preferred_language_id' => $this->languages[$externalLanguageId] ?? null,
            'address' => data_get($patientData, 'address'),
            'city' => data_get($patientData, 'city'),
            'state' => $this->convertState(data_get($patientData, 'state')),
            'zip' => data_get($patientData, 'postal_code'),
            'subscriber_id' => prepare_mrn((string)data_get($patientData, 'mrn')),
            'status_id' => PatientStatus::getNewId(),
        ], $phones));
    }
    
    public function createPatientInOfficeAlly($patientData)
    {
        $phones = [
            'phone_number' => data_get($patientData, 'phone_number'),
            'alternate_phone_number' => data_get($patientData, 'alternate_phone_number'),
            'alternate_phone_number_2' => data_get($patientData, 'alternate_phone_number_2')
        ];
        $phones = $this->parsePhoneNumbers($phones);

        $dateOfBirthday = Carbon::parse(data_get($patientData, 'dob'));

        $data = [
            'first_name' => sanitize_name(data_get($patientData, 'first_name')),
            'last_name' => sanitize_name(data_get($patientData, 'last_name')),
            'middle_initial' => title_case(data_get($patientData, 'middle_initial')),
            'email' => data_get($patientData, 'email'),
            'sex' => data_get($patientData, 'sex'),
            'date_of_birth' => $dateOfBirthday,
            'dob' => [
                'month' => $dateOfBirthday->month,
                'day' => $dateOfBirthday->day,
                'year' => $dateOfBirthday->year,
            ],
            'formatted_dob' => $dateOfBirthday->format('m/d/Y'),
            'address' => data_get($patientData, 'address'),
            'city' => data_get($patientData, 'city'),
            'zip' => data_get($patientData, 'postal_code'),

            'preferred_language_id' => data_get($patientData, 'preferred_language_id'),
            'race' => data_get($patientData, 'race'),
            'state' => $this->convertState(data_get($patientData, 'state')),
            'mrn' => prepare_mrn((string)data_get($patientData, 'mrn')),
        ];

        $data = array_merge($data, $phones);
       
        $officeAllyHelper = new \App\Helpers\Sites\OfficeAlly\OfficeAllyHelper(Option::OA_ACCOUNT_2);
        
        return $officeAllyHelper->createPatient($data);
    }
    
    /**
     * @param array $phones
     * @param bool  $asString
     *
     * @return array
     */
    private function parsePhoneNumbers(array $phones, bool $asString = false): array
    {
        $data       = [];
        $phonesKeys = ['cell_phone', 'home_phone', 'work_phone'];
        foreach (array_filter($phones) as $phone) {
            $matches = [];
            preg_match('/\(?(\d+)\)?[-\s]*(\d+)[-\s]*(\d+)/', $phone, $matches);

            if (count($matches) >= 4 && count($phonesKeys) > 0) {
                if ($asString) {
                    $data[array_shift($phonesKeys)] = sprintf('%s-%s-%s', (string) $matches[1], (string) $matches[2], (string) $matches[3]);
                } else {
                    $data[array_shift($phonesKeys)] = [
                        'area_code' => $matches[1],
                        'prefix'    => $matches[2],
                        'number'    => $matches[3]
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    protected function convertState($name)
    {
        if (!$name) {
            return null;
        }

        $states = [
            ['name' => 'Alabama', 'abbr' => 'AL'],
            ['name' => 'Alaska', 'abbr' => 'AK'],
            ['name' => 'Arizona', 'abbr' => 'AZ'],
            ['name' => 'Arkansas', 'abbr' => 'AR'],
            ['name' => 'California', 'abbr' => 'CA'],
            ['name' => 'Colorado', 'abbr' => 'CO'],
            ['name' => 'Connecticut', 'abbr' => 'CT'],
            ['name' => 'Delaware', 'abbr' => 'DE'],
            ['name' => 'Florida', 'abbr' => 'FL'],
            ['name' => 'Georgia', 'abbr' => 'GA'],
            ['name' => 'Hawaii', 'abbr' => 'HI'],
            ['name' => 'Idaho', 'abbr' => 'ID'],
            ['name' => 'Illinois', 'abbr' => 'IL'],
            ['name' => 'Indiana', 'abbr' => 'IN'],
            ['name' => 'Iowa', 'abbr' => 'IA'],
            ['name' => 'Kansas', 'abbr' => 'KS'],
            ['name' => 'Kentucky', 'abbr' => 'KY'],
            ['name' => 'Louisiana', 'abbr' => 'LA'],
            ['name' => 'Maine', 'abbr' => 'ME'],
            ['name' => 'Maryland', 'abbr' => 'MD'],
            ['name' => 'Massachusetts', 'abbr' => 'MA'],
            ['name' => 'Michigan', 'abbr' => 'MI'],
            ['name' => 'Minnesota', 'abbr' => 'MN'],
            ['name' => 'Mississippi', 'abbr' => 'MS'],
            ['name' => 'Missouri', 'abbr' => 'MO'],
            ['name' => 'Montana', 'abbr' => 'MT'],
            ['name' => 'Nebraska', 'abbr' => 'NE'],
            ['name' => 'Nevada', 'abbr' => 'NV'],
            ['name' => 'New Hampshire', 'abbr' => 'NH'],
            ['name' => 'New Jersey', 'abbr' => 'NJ'],
            ['name' => 'New Mexico', 'abbr' => 'NM'],
            ['name' => 'New York', 'abbr' => 'NY'],
            ['name' => 'North Carolina', 'abbr' => 'NC'],
            ['name' => 'North Dakota', 'abbr' => 'ND'],
            ['name' => 'Ohio', 'abbr' => 'OH'],
            ['name' => 'Oklahoma', 'abbr' => 'OK'],
            ['name' => 'Oregon', 'abbr' => 'OR'],
            ['name' => 'Pennsylvania', 'abbr' => 'PA'],
            ['name' => 'Rhode Island', 'abbr' => 'RI'],
            ['name' => 'South Carolina', 'abbr' => 'SC'],
            ['name' => 'South Dakota', 'abbr' => 'SD'],
            ['name' => 'Tennessee', 'abbr' => 'TN'],
            ['name' => 'Texas', 'abbr' => 'TX'],
            ['name' => 'Utah', 'abbr' => 'UT'],
            ['name' => 'Vermont', 'abbr' => 'VT'],
            ['name' => 'Virginia', 'abbr' => 'VA'],
            ['name' => 'Washington', 'abbr' => 'WA'],
            ['name' => 'West Virginia', 'abbr' => 'WV'],
            ['name' => 'Wisconsin', 'abbr' => 'WI'],
            ['name' => 'Wyoming', 'abbr' => 'WY'],
            ['name' => 'Virgin Islands', 'abbr' => 'V.I.'],
            ['name' => 'Guam', 'abbr' => 'GU'],
            ['name' => 'Puerto Rico', 'abbr' => 'PR']
        ];

        return array_get(array_first($states, function ($state) use ($name) {
            return $state['name'] == $name;
        }, []), 'abbr');
    }
}
