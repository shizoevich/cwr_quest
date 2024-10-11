<?php

namespace App\Helpers;

use App\Helpers\Sites\Tridiuum;
use App\Models\Language;
use App\Option;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class TridiuumHelper
{
    public const REPORT_TYPE_ASSESSMENT = 'Assessment';
    public const REPORT_TYPE_NOTE = 'Note';

    /** @var Tridiuum */
    public $tridiuum;

    /** @var mixed */
    protected $enabled;

    /**
     * TridiuumHelper constructor.
     *
     * @param $login
     * @param $password
     */
    public function __construct()
    {
        $this->enabled = config('parser.tridiuum.enabled', false);
        [$login, $password] = $this->getCredentials();
        $this->tridiuum = new Tridiuum($login, $password);
    }
    
    /**
     * @return array
     */
    private function getCredentials()
    {
        // use dev_tridiuum_credentials for testing
        $credentials = Option::getOptionValue('tridiuum_credentials');
        // $credentials = Option::getOptionValue('dev_tridiuum_credentials');
        // ---

        $credentials = json_decode($credentials, true);
        $defaultAccount = config('tridiuum.default_account');
        
        return [
            $credentials[$defaultAccount]['login'],
            decrypt($credentials[$defaultAccount]['password']),
        ];
    }
    
    /**
     * @param Carbon      $from
     * @param Carbon      $to
     * @param string|null $providerId
     *
     * @return bool|Collection
     */
    public function getAvailability(Carbon $from, Carbon $to, string $providerId = null)
    {
        if ($this->enabled) {
            if (!$this->tridiuum->isLoggedIn()) {
                if (!$this->tridiuum->login()) {
                    return false;
                }
            }

            $tridiuumAvailabilities = $this->tridiuum->getAvailabilities($from->copy()->subDays(2), $to->copy()->addDays(4), 500, 0, $providerId);

            return collect(data_get($tridiuumAvailabilities, 'data', []))
                ->filter(function ($tridiuumAvailabilityData) use ($from, $to) {
                    $tridiuumAvailabilityStart = Carbon::parse(data_get($tridiuumAvailabilityData, 'start_date'));
                    $tridiuumAvailabilityEnd = Carbon::parse(data_get($tridiuumAvailabilityData, 'end_date'));

                    return ($from->isBetween($tridiuumAvailabilityStart, $tridiuumAvailabilityEnd) && $from->ne($tridiuumAvailabilityEnd))
                        || ($to->isBetween($tridiuumAvailabilityStart, $tridiuumAvailabilityEnd) && $to->ne($tridiuumAvailabilityStart))
                        || ($tridiuumAvailabilityStart->isBetween($from, $to) && $tridiuumAvailabilityStart->ne($to))
                        || ($tridiuumAvailabilityEnd->isBetween($from, $to) && $tridiuumAvailabilityEnd->ne($from));
                })->sortBy('start_date');
        }

        return false;
    }
    
    /**
     * @return array|bool
     */
    public function getProviders()
    {
        if ($this->enabled) {
            if (!$this->tridiuum->isLoggedIn()) {
                if (!$this->tridiuum->login()) {
                    return false;
                }
            }
            
            return $this->tridiuum->getProviders();
        }
        
        return false;
    }
    
    /**
     * @param Carbon      $from
     * @param Carbon      $to
     * @param string|null $providerId
     *
     * @return bool|Collection
     */
    public function getAppointments(Carbon $from, Carbon $to, string $providerId = null)
    {
        if ($this->enabled) {
            if (!$this->tridiuum->isLoggedIn()) {
                if (!$this->tridiuum->login()) {
                    return false;
                }
            }

            $tridiuumAppointments = collect();

            $offset = 0;
            $limit = 500;

            do {
                $data = $this->tridiuum->getAppointments($from->copy(), $to->copy(), $limit, $offset, $providerId);

                $tridiuumAppointments = $tridiuumAppointments->merge(
                    collect(data_get($data, 'data', []))
                        ->filter(function ($tridiuumAppointmentData) use ($from, $to) {
                            $tridiuumAppointmentStart = Carbon::parse(data_get($tridiuumAppointmentData, 'start_date'));
                            $tridiuumAppointmentEnd = Carbon::parse(data_get($tridiuumAppointmentData, 'end_date'));

                            return ($from->isBetween($tridiuumAppointmentStart, $tridiuumAppointmentEnd) && $from->ne($tridiuumAppointmentEnd))
                                || ($to->isBetween($tridiuumAppointmentStart, $tridiuumAppointmentEnd) && $to->ne($tridiuumAppointmentStart))
                                || ($tridiuumAppointmentStart->isBetween($from, $to) && $tridiuumAppointmentStart->ne($to))
                                || ($tridiuumAppointmentEnd->isBetween($from, $to) && $tridiuumAppointmentEnd->ne($from));
                        })->sortBy('start_date')
                );

                $offset += $limit;
            } while (data_get($data, 'meta.total', 0) > $offset);

            return $tridiuumAppointments;
        }

        return false;
    }

    /**
     * @param int $start
     * @param int $limit
     *
     * @return array|mixed
     */
    public function getPatientsInfo(int $start = 0, $limit = 1000)
    {
        if (!$this->enabled) {
            return [];
        }

        if (!$this->tridiuum->isLoggedIn()) {
            if (!$this->tridiuum->login()) {
                return [];
            }
        }
        $patients = $this->tridiuum->getPatients($limit, $start);

        return $patients;
    }

     private function getFromObjectPatientValue($mrn, $fieldName, $param)
     {
         $patients = $this->tridiuum->getPatients(10, 0, $mrn);
         $patient_id = data_get($patients, 'data.0.patient_id', []);
         $data = $this->tridiuum->getPatientData($patient_id);
         
         $valueData = data_get($data, 'fields.'.$fieldName.'.options', []);
         $valueArray = [];
         foreach($valueData as $item)
         {
             if(data_get($item, 'value', []) == $param){
                $valueArray[] = [$fieldName => data_get($item, 'label', [])];
              }
         }
         if (isset($valueArray['0'][$fieldName])){
            return  $valueArray['0'][$fieldName];
         }else{
            return null;
         }
     }

    /**
     * @param string|null $sexId
     *
     * @return string
     */
    private function getSex($sexId)
    {
        switch ($sexId) {
            case 'a98236d2-8dac-4635-be4f-ae4fda670d66':
                return 'F'; //Female
            case 'f7c9e1e2-2524-44ce-9d77-185df18ce5f7':
                return 'M'; //Male
            case '0d9f109f-8f7e-42e6-9c3f-d8dbba81b2dc':
                return 'U'; 
            default:
                return 'U';
        }
    }

    /**
     * @param $mrn
     *
     * @param bool $withValues
     *
     * @return bool|mixed
     */
    public function getPatientInfo($mrn, $withValues = true)
    {
        if (!$this->enabled) {
            return false;
        }

        if (!$this->tridiuum->isLoggedIn()) {
            if (!$this->tridiuum->login()) {
                return false;
            }
        }

        $patients = $this->tridiuum->getPatients(10, 0, $mrn);
        $patientsData = data_get($patients, 'data.0.DT_RowData', []);
        
        $patient_id = data_get($patients, 'data.0.patient_id', []);
        $data = $this->tridiuum->getPatientData($patient_id);

        //new code

        $basicPath = 'patient_data.0.DT_RowData.';

        $external_id = data_get($data, $basicPath.'id', []);
        $first_name = data_get($data, $basicPath.'first_name', []);
        $last_name = data_get($data, $basicPath.'last_name', []);
        $middle_initial = !empty(data_get($data, $basicPath.'middle_initial', [])) ? data_get($data, $basicPath.'middle_initial', []) : null;
        $mrn = data_get($data, $basicPath.'mrn', []);
        $dob = data_get($data, $basicPath.'dob', []);
        $address =  data_get($data, $basicPath.'address', []);
        $city =  data_get($data, $basicPath.'city', []);
        $postal_code = data_get($data, $basicPath.'postal_code', []);
        $active = data_get($data, $basicPath.'active', []);
        $sex = $this->getSex(data_get($data, $basicPath.'sex', []));
 
        if(!empty(data_get($data,$basicPath.'email', [])) ){
           $email = data_get($data,$basicPath.'email', []);
        }
        elseif(!empty(data_get($data,$basicPath.'notification_email', [])))
        {
           $email = data_get($data, $basicPath.'notification_email', []);
        }
        else{
           $email = null;
        }

        if(!empty(data_get($data, $basicPath.'phone_number', []))){
            $phone_number = data_get($data, $basicPath.'phone_number', []);
        } 
        elseif(!empty(data_get($data, $basicPath.'alternate_phone_number', [])))
        {
            $phone_number = data_get($data, $basicPath.'alternate_phone_number', []);
        } 
        elseif(!empty( data_get($data, $basicPath.'alternate_phone_number_2', [])))
        {
            $phone_number = data_get($data, $basicPath.'alternate_phone_number_2', []);
        }else{
            $phone_number = null;
        }
        
        $alternate_phone_number = data_get($data, $basicPath.'alternate_phone_number', []);
        $alternate_phone_number_2 = data_get($data, $basicPath.'alternate_phone_number_2', []);
        $language = Language::where('tridiuum_id', data_get($data, $basicPath.'language', ''))->first();
        // race, ethnicity and state fields parsing below is deprecated as language once was.
        // At the moment parsed value is id from tridiuum. List of values you can find at https://polestarapp.com/filters/org_specific_fields_patient_edit
        // also they are saved at therapist_survey_races and therapist_survey_ethnicities tables.
        $race =   $this->getFromObjectPatientValue($mrn,'race',data_get($data, $basicPath.'race', []));
        $ethnicity =  $this->getFromObjectPatientValue($mrn,'ethnicity',data_get($data, $basicPath.'ethnicity', []));
        $state = $this->getFromObjectPatientValue($mrn,'state',data_get($data, $basicPath.'state', []));
         
        //old code
        $patientsValues = [];
        if ($withValues) {
            $patientsValues = $this->getPatientValues(array_only($patientsData, [
                'site', 'sex', 'counselor_id', 'race', 'ethnicity', 'language', 'state'
            ]));
        }
      
        return array_merge(
            [
                'external_id'=> $external_id,
                'first_name'=> trim($first_name),
                'last_name'=> trim($last_name),
                'middle_initial'=> trim($middle_initial),
                'mrn'=> $mrn,
                'dob'=> $dob, 
                'sex'=> $sex,
                'email' => $email,
                'phone_number' => $phone_number,
                'alternate_phone_number' => $alternate_phone_number,
                'alternate_phone_number_2' => $alternate_phone_number_2,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postal_code,
                'race' => $race,
                'ethnicity' => $ethnicity,
                'preferred_language_id' => optional($language)->officeally_id ,
                'state' => $state,
                'active' => $active,
            ]
            //$patientsData,
            //$patientsValues
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getPatientValues(array $data)
    {
        $values = [];
        $inputKeys = [
            'site' => 'primary_site',
            'counselor_id' => 'counselor_group',
        ];
        $patientsPage = $this->tridiuum->getPatientsPage();

        $crawler = new Crawler($patientsPage);
        $crawler = $crawler->filter('#patient-modal');
        foreach ($data as $key => $value) {
            $inputKey = $key;
            if (array_key_exists($key, $inputKeys)) {
                $inputKey = $inputKeys[$key];
            }

            $input = $crawler->filter("select[name='{$inputKey}'] > option[value='{$value}']");
            $values["{$key}_value"] = $input->count() > 0 ? $input->text() : null;
        }

        return $values;
    }

    /**
     * @return bool
     */
    public function isCredentialsInvalid()
    {
        if (!$this->enabled) {
            return false;
        }

        if (!$this->tridiuum->isLoggedIn()) {
            $this->tridiuum->login();
        }

        return $this->tridiuum->isCredentialsInvalid();
    }

    /**
     * @param $patientId
     *
     * @return mixed
     */
    public function getCurrentTrack($patientId)
    {
        if (!$this->enabled) {
            return false;
        }

        if (!$this->tridiuum->isLoggedIn()) {
            if (!$this->tridiuum->login()) {
                return false;
            }
        }

        $getPatientDetailTracksData = json_decode($this->tridiuum->getPatientDetailTrack($patientId), true);

        $currentTrack = isset($getPatientDetailTracksData['tracks_detail']['tracks_list']['main_tracks'][0]['tracks'][0]['id'])
            ? $getPatientDetailTracksData['tracks_detail']['tracks_list']['main_tracks'][0]['tracks'][0]['id']
            : null;
    
        return $currentTrack;
    }

    /**
     * @param $currentTrack
     *
     * @return mixed
     */
    public function getAssessmentsDocuments($currentTrack)
    {
        if (!$this->enabled) {
            return false;
        }

        if (!$this->tridiuum->isLoggedIn()) {
            if (!$this->tridiuum->login()) {
                return false;
            }
        }

        $response = $this->tridiuum->getAssessmentsDocuments($currentTrack);
        $documents = [];

        $surveys = data_get($response, 'all_surveys') ?? [];

        foreach ($surveys as $survey) {
            $completedDate = data_get($survey, 'ordering_time');
            if($completedDate) {
                $completedDate = Carbon::parse($completedDate)->timezone(config('app.timezone'))->toDateTimeString();
            } else {
                $completedDate = Carbon::now()->toDateTimeString();
            }
            if(isset($survey['reports'])) {
                $documents = array_merge($documents, array_map(function ($report) use ($completedDate) {
                    return [
                        'id' => data_get($report, 'id'),
                        'name' => data_get($report, 'name'),
                        'created_at' => $completedDate,
                        'type' => self::REPORT_TYPE_ASSESSMENT,
                    ];
                }, $survey['reports']));
            }
        }

        return $documents;
    }

    /**
     * @param $patientId
     * @param $currentTrack
     *
     * @return mixed
     */
    public function getNotesDocuments($patientId, $currentTrack)
    {
        if (!$this->enabled) {
            return false;
        }

        if (!$this->tridiuum->isLoggedIn()) {
            if (!$this->tridiuum->login()) {
                return false;
            }
        }

        $documents = $this->tridiuum->getNotesDocuments($patientId, $currentTrack);

        return array_map(function ($document) {
            $completedDate = data_get($document, 'ordering_time');
            if($completedDate) {
                $completedDate = Carbon::parse($completedDate)->timezone(config('app.timezone'))->toDateTimeString();
            } else {
                $completedDate = Carbon::now()->toDateTimeString();
            }
            return [
                'id' => data_get($document, 'report_id'),
                'name' => data_get($document, 'note_type'),
                'logged_by' => data_get($document, 'logged_by'),
                'created_at' => $completedDate,
                'type' => self::REPORT_TYPE_NOTE,
            ];
        }, $documents);
    }

    /**
     * @param $documentId
     *
     * @return mixed
     */
    public function downloadDocument($documentId)
    {
        if (!$this->enabled) {
            return false;
        }

        if (!$this->tridiuum->isLoggedIn()) {
            if (!$this->tridiuum->login()) {
                return false;
            }
        }

        $file = $this->tridiuum->downloadDocument($documentId);
        $extension = 'pdf';
        $newFileName = md5(uniqid(time())) . '.' . $extension;
        $status = false;
        if($file !== 'No Report Found') {
            $status = \Storage::disk('patients_docs')->put($newFileName, $file);
        }

        return [
            'status' => $status,
            'filename' => $newFileName
        ];
    }
}
