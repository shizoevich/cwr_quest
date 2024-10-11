<?php

namespace App\Helpers\Sites\OfficeAlly\Traits;

use App\Models\Officeally\OfficeallyLog;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use voku\helper\HtmlDomParser;

/**
 * Trait Patients
 * @package App\Helpers\Sites\OfficeAlly\Traits
 */
trait Patients
{
    /**
     * @param array $filters
     *
     * @return mixed|null
     */
    public function getPatientList(array $filters = [])
    {
        $urlFilter = [
            'rules' => array_merge($filters, [
                [
                    'field' => 'MaxRecords',
                    'data' => 500000,
                ],
                [
                    'field' => 'StatusID',
                    'data' => '0',
                ],
            ]),
        ];
        $uri = 'ManagePatients/ManagePatients.aspx?Tab=P&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&_search=true&nd=1504512216235&rows=500000&page=1&sidx=&sord=&filters2=' . urlencode(json_encode($urlFilter, JSON_UNESCAPED_SLASHES));

        $response = $this->officeAlly->get($uri, [], true);
        $patients = json_decode($response->getBody()->getContents(), true);
        if (empty($patients) && !is_null($patients)) {
            return [];
        }
        $patients = data_get($patients, 'rows');
        if ($patients === null) {
            $this->officeAlly->notifyIfFailed('Patients list is not parsed.');
        }

        return $patients;
    }

    /**
     * @param int $patientId
     *
     * @return mixed
     */
    public function getPatientProfile($patientId)
    {
        $response = $this->officeAlly->get("ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID={$patientId}", [], true);

        return $response->getBody()->getContents();
    }

    /**
     * @param int $patientId
     *
     * @return mixed
     */
    public function getPatientAlerts($patientId)
    {
        if (!$this->isProduction()) {
            return [];
        }
        
        $response = $this->officeAlly->get("CommonUserControls/Alerts/GridApi.aspx?jqGridID=AlertsGrid&_search=false&rows=10000&page=1&sidx=&sord=asc&PID={$patientId}", [], true);
        $alerts = json_decode($response->getBody()->getContents(), true);
        $alerts = data_get($alerts, 'rows');
        if ($alerts === null) {
            $this->officeAlly->notifyIfFailed("Patient Alert list is not parsed (Patient ID: {$patientId}).");
        }

        return $alerts;
    }

    public function postPatientAlerts($patientExterlnalId, $alertMessage)
    {
       
        if (!$this->isProduction()) {
            return;
        }

        $payload = [
            'id'                         => '0',
            'pid'                        => $patientExterlnalId,
            's'                          => '1',
            'msg'                         => $alertMessage,
            'd'                          => '8',
            'os'                     => '-1',
            '__RequestVerificationToken' => $this->getRequestVerificationToken(),
        ];

        $response = $this->officeAlly->post("CommonUserControls/Alerts/Api.aspx?oper=UpdateAlert", [
            'headers' => [
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
            ],
            'json' => $payload,
        ], true);

        $appointment = json_decode($response->getBody()->getContents(), true);

        if ($appointment['Message'] == 'Alert added successfully!') {
            $this->log(OfficeallyLog::ACTION_POST_ALLERT, true, ['patient_id' => $patientExterlnalId,'message' => $appointment['Message'] ]);
            return true;
        }

    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param        $patientData
     */
    private function getExistingPatientId(string $firstName, string $lastName, $patientData)
    {
        $patients = $this->getPatientList([
            [
                'field' => 'SearchBy',
                'data' => 'PatientLastName',
            ],
            [
                /**
                 * 0 - Starts With
                 * 1 - Equals To
                 * 2 - Contains
                 * 3 - Range
                 */
                'field' => 'SearchCondition',
                'data' => '1',
            ],
            [
                'field' => 'TextField',
                'data' => $lastName,
            ],
            [
                'field' => 'SearchByAdv',
                'data' => 'PatientFirstName',
            ],
            [
                /**
                 * 0 - Starts With
                 * 1 - Equals To
                 * 2 - Contains
                 * 3 - Range
                 */
                'field' => 'SearchConditionAdv',
                'data' => '1',
            ],
            [
                'field' => 'TextFieldAdv',
                'data' => $firstName,
            ],
        ]);
        foreach ($patients as $patient) {
            if ($patient['cell'][1] !== $lastName || $patient['cell'][2] !== $firstName) {
                continue;
            }
            if (!data_get($patientData, 'date_of_birth') || $patientData['date_of_birth']->format('m/d/Y') === $patient['cell'][7]) {
                return $patient['cell'][0];
            }
        }

        return null;
    }

    /**
     * @param array $patientData
     */
    public function createPatient(array $patientData)
    {
        if (!$this->isProduction()) {
            return rand(1, 104000000);
        }
        $firstName = data_get($patientData, 'first_name');
        $lastName = data_get($patientData, 'last_name');
        if (empty($firstName) || empty($lastName)) {
            $message = 'Patient not created. First name or last name is empty.';
            $log = $this->log(OfficeallyLog::ACTION_CREATE_PATIENT, false, $patientData, $message);
            $this->officeAlly->notifyIfFailed(sprintf('[Log ID: %s] %s', (string) data_get($log, 'id'), $message));

            return null;
        }

        $patientId = $this->getExistingPatientId($firstName, $lastName, $patientData);
        if ($patientId) {
            $this->log(OfficeallyLog::ACTION_CREATE_PATIENT, true, $patientData, 'Patient already exists.');

            return $patientId;
        }

        //build payload
        $page = $this->officeAlly->get('ManagePatients/EditPatient.aspx?PageAction=add&Tab=P#/applet-wrapper/recurring-payments/0/payments/notenrolled', [], true)->getBody()->getContents();
        $crawler = new Crawler($page);
        $payload = [];
        $crawler->filter('form#aspnetForm input, form#aspnetForm textarea')->each(function ($node) use (&$payload) {
            $inputType = $node->attr('type');
            $inputName = $node->attr('name');
            if ($inputType !== 'button' && $inputType !== 'submit' && !empty($inputName)) {
                $payload[$inputName] = $this->prepareValue($node->attr('value'));
            }
        });
        $crawler->filter('form#aspnetForm select')->each(function ($node) use (&$payload) {
            $inputName = $node->attr('name');
            if (empty($inputName)) {
                return;
            }
            try {
                $val = $node->filter('option[selected="selected"]')->first()->attr('value');
            } catch (InvalidArgumentException $e) {
                $val = null;
            }

            $payload[$inputName] = $this->prepareValue($val);
        });
        $payload['ctl00$phFolderContent$ucPatient$LastName'] = $lastName;
        $payload['ctl00$phFolderContent$ucPatient$FirstName'] = $firstName;
        if (data_get($patientData, 'middle_initial')) {
            $payload['ctl00$phFolderContent$ucPatient$MiddleName'] = $patientData['middle_initial'];
        }
        if (data_get($patientData, 'date_of_birth')) {
            $payload['ctl00$phFolderContent$ucPatient$DOB$Month'] = $patientData['date_of_birth']->format('m'); //e.g. 04
            $payload['ctl00$phFolderContent$ucPatient$DOB$Day'] = $patientData['date_of_birth']->format('d'); //e.g. 01
            $payload['ctl00$phFolderContent$ucPatient$DOB$Year'] = $patientData['date_of_birth']->format('Y'); //e.g. 1999
        }
        if (data_get($patientData, 'preferred_language_id')) {
            $payload['ctl00$phFolderContent$ucPatient$ddlLanguage'] = $patientData['preferred_language_id'];
        }

        if (data_get($patientData, 'sex')) {
            $allowedGenders = [
                'M', //male
                'F', //female
                'U', //unknown
            ];
            $patientData['sex'] = strtoupper($patientData['sex']);
            if (!in_array($patientData['sex'], $allowedGenders, false)) {
                $payload['ctl00$phFolderContent$ucPatient$lstGender'] = 'U';
            } else {
                $payload['ctl00$phFolderContent$ucPatient$lstGender'] = $patientData['sex'];
            }
        }
        if (data_get($patientData, 'email')) {
            $payload['ctl00$phFolderContent$ucPatient$Email'] = $patientData['email'];
        }
        if (data_get($patientData, 'cell_phone')) {
            $payload['ctl00$phFolderContent$ucPatient$CellPhone$AreaCode'] = (string) data_get($patientData['cell_phone'], 'area_code');
            $payload['ctl00$phFolderContent$ucPatient$CellPhone$Prefix'] = (string) data_get($patientData['cell_phone'], 'prefix');
            $payload['ctl00$phFolderContent$ucPatient$CellPhone$Number'] = (string) data_get($patientData['cell_phone'], 'number');
        }
        if (data_get($patientData, 'home_phone')) {
            $payload['ctl00$phFolderContent$ucPatient$HomePhone$AreaCode'] = (string) data_get($patientData['home_phone'], 'area_code');
            $payload['ctl00$phFolderContent$ucPatient$HomePhone$Prefix'] = (string) data_get($patientData['home_phone'], 'prefix');
            $payload['ctl00$phFolderContent$ucPatient$HomePhone$Number'] = (string) data_get($patientData['home_phone'], 'number');
        }
        if (data_get($patientData, 'work_phone')) {
            $payload['ctl00$phFolderContent$ucPatient$WorkPhone$AreaCode'] = (string) data_get($patientData['work_phone'], 'area_code');
            $payload['ctl00$phFolderContent$ucPatient$WorkPhone$Prefix'] = (string) data_get($patientData['work_phone'], 'prefix');
            $payload['ctl00$phFolderContent$ucPatient$WorkPhone$Number'] = (string) data_get($patientData['work_phone'], 'number');
        }
        if (data_get($patientData, 'preferred_phone')) {
            $payload['ctl00$phFolderContent$ucPatient$lstPreferredPhone'] = $patientData['preferred_phone'];
        }
        if (data_get($patientData, 'address')) {
            $payload['ctl00$phFolderContent$ucPatient$AddressLine1'] = $patientData['address'];
        }
        if (data_get($patientData, 'address_2')) {
            $payload['ctl00$phFolderContent$ucPatient$AddressLine2'] = $patientData['address_2'];
        }
        if (data_get($patientData, 'city')) {
            $payload['ctl00$phFolderContent$ucPatient$City'] = $patientData['city'];
        }
        if (data_get($patientData, 'state')) {
            $payload['ctl00$phFolderContent$ucPatient$lstState'] = $patientData['state'];
        }
        if (data_get($patientData, 'zip')) {
            $payload['ctl00$phFolderContent$ucPatient$Zip'] = $patientData['zip'];
        }
        if (data_get($patientData, 'primary_care_provider')) {
            $payload['ctl00$phFolderContent$ucPatient$hdnPrimaryProviderID'] = $patientData['primary_care_provider'];
        }

        if (data_get($patientData, 'insurance_id')) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceID'] = $patientData['insurance_id'];
        }
        if (data_get($patientData, 'mrn')) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceSubscriberID'] = $patientData['mrn'];
        }
        if (data_get($patientData, 'plan_name')) {
            $payload['ctl00$phFolderContent$ucPatient$InsurancePlanName'] = $patientData['plan_name'];
        }
        if (data_get($patientData, 'visit_copay')) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceVisitCopay'] = $patientData['visit_copay'];
        }
        if (data_get($patientData, 'deductible')) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceDeductible'] = $patientData['deductible'];
        }
        if (data_get($patientData, 'eligibility_payer_id')) {
            $payload['ctl00$phFolderContent$ucPatient$BatchEligibilityPayerID'] = $patientData['eligibility_payer_id'];
        }
        if(data_get($patientData, 'auth_number')) {
            $payload['ctl00$phFolderContent$ucPatient$PriorAuthorizationNumber'] = $patientData['auth_number'];
        }
        if (data_get($patientData, 'visits_auth')) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$NumberOfVisitsAuthorized'] = $patientData['visits_auth'];
        }
        if (data_get($patientData, 'visits_auth_left')) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$NumberOfVisitsLeft'] = $patientData['visits_auth_left'];
        }
        if (data_get($patientData, 'eff_start_date')) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStartDate$Month'] = $patientData['eff_start_date']->format('m'); //e.g. 04
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStartDate$Day'] = $patientData['eff_start_date']->format('d'); //e.g. 01
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStartDate$Year'] = $patientData['eff_start_date']->format('Y');
        }
        if (data_get($patientData, 'eff_stop_date')) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStopDate$Month'] = $patientData['eff_stop_date']->format('m'); //e.g. 04
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStopDate$Day'] = $patientData['eff_stop_date']->format('d'); //e.g. 01
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStopDate$Year'] = $patientData['eff_stop_date']->format('Y');
        }
        /**
         * Update patient diagnoses
         */
        if (array_key_exists('diagnoses', $patientData)) {
            //clear all diagnoses
            foreach ($payload as $key => $value) {
                if (starts_with($key, 'ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dc_10_') || starts_with($key, 'ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dd_10_')) {
                    $payload[$key] = '';
                }
            }
            $availableDiagnosePointers = range('A', 'D'); // From A to D letters
            $diagnosePointers = '';
            $i = 1;
            foreach ($patientData['diagnoses'] as $diagnose) {
                $payload['ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dc_10_' . $i] = $diagnose['code'];
                $payload['ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dd_10_' . $i] = $diagnose['description'];
                if (isset($availableDiagnosePointers[$i - 1])) {
                    $diagnosePointers .= $availableDiagnosePointers[$i - 1];
                }
                $i++;
            }

            $template = json_decode(data_get($payload, 'ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$hdnJsLoadTemplateLineItem'), true);
            if (isset($template[0])) {
                $template[0]['PatientTemplateDiagnosisCode'] = $diagnosePointers;
            } else {
                $template[0] = [
                    'PatientTemplateLineItemNo' => '1',
                    'PatientTemplateCPT' => '',
                    'PatientTemplatePlaceOfService' => '',
                    'PatientTemplateModifierA' => '',
                    'PatientTemplateModifierB' => '',
                    'PatientTemplateModifierC' => '',
                    'PatientTemplateModifierD' => '',
                    'PatientTemplateDiagnosisCode' => $diagnosePointers,
                    'PatientTemplateCharge' => '',
                    'PatientTemplateQuantity' => '1',
                    'PatientTemplateDescription' => '',
                ];
            }
            $payload['ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$hdnJsUpdateTemplateLineItem'] = json_encode($template);
            $payload['ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$ucPatientTemplateLineItem$PatientTemplateDiagnosisCode0'] = $diagnosePointers;
        }

        if (array_key_exists('billable_lines', $patientData)) {
            $template = [];
            foreach ($patientData['billable_lines'] as $key => $item) {
                $template[] = [
                    'PatientTemplateLineItemNo' => $key + 1,
                    'PatientTemplateCPT' => $item['cpt'] ?? '',
                    'PatientTemplatePlaceOfService' => $item['pos'] ?? '',
                    'PatientTemplateModifierA' => $item['modifier_a'] ?? '',
                    'PatientTemplateModifierB' => $item['modifier_b'] ?? '',
                    'PatientTemplateModifierC' => $item['modifier_c'] ?? '',
                    'PatientTemplateModifierD' => $item['modifier_d'] ?? '',
                    'PatientTemplateDiagnosisCode' => $item['diagnose_pointer'] ?? '',
                    'PatientTemplateCharge' => $item['charge'] ?? '',
                    'PatientTemplateQuantity' => '1',
                    'PatientTemplateDescription' => '',
                ];
            }

            $payload['ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$hdnJsUpdateTemplateLineItem'] = json_encode($template);
        }

        $payload['PageAction'] = 'Update';
        $payload['ctl00$phFolderContent$ucPatient$btnTriggerSave'] = 1;
        $response = $this->officeAlly->post("ManagePatients/EditPatient.aspx?PageAction=add&Tab=P", [
            'form_params' => $payload,
        ]);
        $locationHeader = data_get($response->getHeader('Location'), '0');
        //check response
        if ($response->getStatusCode() === 302 && $locationHeader && preg_match('/^\/pm\/ManagePatients\/ManagePatients\.aspx/', $locationHeader)) {
            $this->log(OfficeallyLog::ACTION_CREATE_PATIENT, true, $patientData);
        } else {
            $message = 'Patient not created.';
            $log = $this->log(OfficeallyLog::ACTION_CREATE_PATIENT, false, $patientData, $message);
            $this->officeAlly->notifyIfFailed(sprintf('[Log ID: %s] %s', (string) data_get($log, 'id'), $message));

            return null;
        }

        return $this->getExistingPatientId($firstName, $lastName, $patientData);
    }

    /**
     * @param int   $patientId
     * @param array $patientData
     */
    public function updatePatient($patientId, array $patientData)
    {
        if (!$this->isProduction() || empty($patientData) || $patientId == 11111111) {
            return;
        }

        //build payload
        $page = $this->officeAlly->get("ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID={$patientId}#/applet-wrapper/recurring-payments/{$patientId}/payments/notenrolled", [], true)->getBody()->getContents();
        $crawler = new Crawler($page);

        if (!$crawler->filter('#ctl00_phFolderContent_ucPatient_lblPatientID')->count()) {
            $message = "Patient doesn't exist.";
            $this->log(OfficeallyLog::ACTION_UPDATE_PATIENT, false, $patientData, $message);

            return;
        }

        $payload = [
            '__EVENTTARGET' => null,
            '__EVENTARGUMENT' => null,
            '__LASTFOCUS' => null,
            '__SCROLLPOSITIONX' => null,
            '__SCROLLPOSITIONY' => null,
        ];
        $crawler->filter('form#aspnetForm input, form#aspnetForm textarea')->each(function ($node) use (&$payload) {
            $inputType = $node->attr('type');
            $inputName = $node->attr('name');
            if ($inputType !== 'button' && $inputType !== 'submit' && !empty($inputName)) {
                $payload[$inputName] = $this->prepareValue($node->attr('value'));
            }
        });
        $crawler->filter('form#aspnetForm select')->each(function ($node) use (&$payload) {
            $inputName = $node->attr('name');
            if (empty($inputName)) {
                return;
            }
            try {
                $val = $node->filter('option[selected="selected"]')->first()->attr('value');
            } catch (InvalidArgumentException $e) {
                $val = null;
            }

            $payload[$inputName] = $this->prepareValue($val);
        });
        if (data_get($patientData, 'first_name')) {
            $payload['ctl00$phFolderContent$ucPatient$FirstName'] = $patientData['first_name'];
        }
        if (data_get($patientData, 'last_name')) {
            $payload['ctl00$phFolderContent$ucPatient$LastName'] = $patientData['last_name'];
        }
        if (array_key_exists('middle_initial', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$MiddleName'] = $patientData['middle_initial'];
        }
        if (data_get($patientData, 'date_of_birth')) {
            $payload['ctl00$phFolderContent$ucPatient$DOB$Month'] = $patientData['date_of_birth']->format('m'); //e.g. 04
            $payload['ctl00$phFolderContent$ucPatient$DOB$Day'] = $patientData['date_of_birth']->format('d'); //e.g. 01
            $payload['ctl00$phFolderContent$ucPatient$DOB$Year'] = $patientData['date_of_birth']->format('Y'); //e.g. 1999
        }
        if (data_get($patientData, 'preferred_language_id')) {
            $payload['ctl00$phFolderContent$ucPatient$ddlLanguage'] = $patientData['preferred_language_id'];
        }
        if (data_get($patientData, 'sex')) {
            $allowedGenders = [
                'M', //male
                'F', //female
                'U', //unknown
            ];
            $patientData['sex'] = strtoupper($patientData['sex']);
            if (!in_array($patientData['sex'], $allowedGenders, false)) {
                $payload['ctl00$phFolderContent$ucPatient$lstGender'] = 'U';
            } else {
                $payload['ctl00$phFolderContent$ucPatient$lstGender'] = $patientData['sex'];
            }
        }
        if (array_key_exists('email', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$Email'] = $patientData['email'];
        }
        if (array_key_exists('preferred_phone', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$lstPreferredPhone'] = $patientData['preferred_phone'];
        }
        if (array_key_exists('address', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$AddressLine1'] = $patientData['address'];
        }
        if (array_key_exists('address_2', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$AddressLine2'] = $patientData['address_2'];
        }
        if (array_key_exists('city', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$City'] = $patientData['city'];
        }
        if (array_key_exists('state', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$lstState'] = $patientData['state'];
        }
        if (array_key_exists('zip', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$Zip'] = $patientData['zip'];
        }
        if (array_key_exists('primary_care_provider', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$hdnPrimaryProviderID'] = $patientData['primary_care_provider'];
        }
        if (array_key_exists('insurance_id', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceID'] = $patientData['insurance_id'];
        }
        if (array_key_exists('mrn', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceSubscriberID'] = $patientData['mrn'];
        }
        if (array_key_exists('plan_name', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$InsurancePlanName'] = $patientData['plan_name'];
        }
        if (array_key_exists('visit_copay', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceVisitCopay'] = $patientData['visit_copay'];
        }
        if (array_key_exists('deductible', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$InsuranceDeductible'] = $patientData['deductible'];
        }
        if (array_key_exists('eligibility_payer_id', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$BatchEligibilityPayerID'] = $patientData['eligibility_payer_id'];
        }
        if (array_key_exists('auth_number', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$PriorAuthorizationNumber'] = $patientData['auth_number'];
        }
        if (array_key_exists('visits_auth', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$NumberOfVisitsAuthorized'] = $patientData['visits_auth'];
        }
        if (array_key_exists('visits_auth_left', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$NumberOfVisitsLeft'] = $patientData['visits_auth_left'];
        }
        if (array_key_exists('eff_start_date', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStartDate$Month'] = isset($patientData['eff_start_date']) ? $patientData['eff_start_date']->format('m') : '';
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStartDate$Day'] = isset($patientData['eff_start_date']) ? $patientData['eff_start_date']->format('d') : '';
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStartDate$Year'] = isset($patientData['eff_start_date']) ? $patientData['eff_start_date']->format('Y') : '';
        }
        if (array_key_exists('eff_stop_date', $patientData)) {
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStopDate$Month'] = isset($patientData['eff_stop_date']) ? $patientData['eff_stop_date']->format('m') : '';
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStopDate$Day'] = isset($patientData['eff_stop_date']) ? $patientData['eff_stop_date']->format('d') : '';
            $payload['ctl00$phFolderContent$ucPatient$Authorization$AuthorizedStopDate$Year'] = isset($patientData['eff_stop_date']) ? $patientData['eff_stop_date']->format('Y') : '';
        }

        /**
         * Update patient diagnoses
         */
        if (array_key_exists('diagnoses', $patientData)) {
            //clear all diagnoses
            foreach ($payload as $key => $value) {
                if (starts_with($key, 'ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dc_10_') || starts_with($key, 'ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dd_10_')) {
                    $payload[$key] = '';
                }
            }
            $availableDiagnosePointers = range('A', 'D'); // From A to D letters
            $diagnosePointers = '';
            $i = 1;
            foreach ($patientData['diagnoses'] as $diagnose) {
                $payload['ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dc_10_' . $i] = $diagnose['code'];
                $payload['ctl00$phFolderContent$ucPatient$ucDiagnosisCodes$dd_10_' . $i] = $diagnose['description'];
                if (isset($availableDiagnosePointers[$i - 1])) {
                    $diagnosePointers .= $availableDiagnosePointers[$i - 1];
                }
                $i++;
            }

            $template = json_decode(data_get($payload, 'ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$hdnJsLoadTemplateLineItem'), true);
            if (isset($template[0])) {
                $template[0]['PatientTemplateDiagnosisCode'] = $diagnosePointers;
            } else {
                $template[0] = [
                    'PatientTemplateLineItemNo' => '1',
                    'PatientTemplateCPT' => '',
                    'PatientTemplatePlaceOfService' => '',
                    'PatientTemplateModifierA' => '',
                    'PatientTemplateModifierB' => '',
                    'PatientTemplateModifierC' => '',
                    'PatientTemplateModifierD' => '',
                    'PatientTemplateDiagnosisCode' => $diagnosePointers,
                    'PatientTemplateCharge' => '',
                    'PatientTemplateQuantity' => '1',
                    'PatientTemplateDescription' => '',
                ];
            }
            $payload['ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$hdnJsUpdateTemplateLineItem'] = json_encode($template);
            $payload['ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$ucPatientTemplateLineItem$PatientTemplateDiagnosisCode0'] = $diagnosePointers;
        }

        /**
         * Update Primary Care Provider
         */
        if (array_key_exists('new_primary_care_provider', $patientData)
            && array_key_exists('delete_primary_care_provider', $patientData)
            && $payload['ctl00$phFolderContent$ucPatient$hdnPrimaryProviderID'] == $patientData['delete_primary_care_provider']) {
            $payload['ctl00$phFolderContent$ucPatient$hdnPrimaryProviderID'] = $patientData['new_primary_care_provider'];
        }

        /**
         * Update patient phone numbers
         */
        if (array_key_exists('cell_phone', $patientData)) {
            if (empty($patientData['cell_phone'])) {
                $payload['ctl00$phFolderContent$ucPatient$CellPhone$AreaCode'] = '';
                $payload['ctl00$phFolderContent$ucPatient$CellPhone$Prefix'] = '';
                $payload['ctl00$phFolderContent$ucPatient$CellPhone$Number'] = '';
            } else {
                $payload['ctl00$phFolderContent$ucPatient$CellPhone$AreaCode'] = $patientData['cell_phone']['area_code'];
                $payload['ctl00$phFolderContent$ucPatient$CellPhone$Prefix'] = $patientData['cell_phone']['prefix'];
                $payload['ctl00$phFolderContent$ucPatient$CellPhone$Number'] = $patientData['cell_phone']['number'];
            }
        }
        if (array_key_exists('home_phone', $patientData)) {
            if (empty($patientData['home_phone'])) {
                $payload['ctl00$phFolderContent$ucPatient$HomePhone$AreaCode'] = '';
                $payload['ctl00$phFolderContent$ucPatient$HomePhone$Prefix'] = '';
                $payload['ctl00$phFolderContent$ucPatient$HomePhone$Number'] = '';
            } else {
                $payload['ctl00$phFolderContent$ucPatient$HomePhone$AreaCode'] = $patientData['home_phone']['area_code'];
                $payload['ctl00$phFolderContent$ucPatient$HomePhone$Prefix'] = $patientData['home_phone']['prefix'];
                $payload['ctl00$phFolderContent$ucPatient$HomePhone$Number'] = $patientData['home_phone']['number'];
            }
        }
        if (array_key_exists('work_phone', $patientData)) {
            if (empty($patientData['work_phone'])) {
                $payload['ctl00$phFolderContent$ucPatient$WorkPhone$AreaCode'] = '';
                $payload['ctl00$phFolderContent$ucPatient$WorkPhone$Prefix'] = '';
                $payload['ctl00$phFolderContent$ucPatient$WorkPhone$Number'] = '';
            } else {
                $payload['ctl00$phFolderContent$ucPatient$WorkPhone$AreaCode'] = $patientData['work_phone']['area_code'];
                $payload['ctl00$phFolderContent$ucPatient$WorkPhone$Prefix'] = $patientData['work_phone']['prefix'];
                $payload['ctl00$phFolderContent$ucPatient$WorkPhone$Number'] = $patientData['work_phone']['number'];
            }
        }

        if (array_key_exists('billable_lines', $patientData)) {
            $template = [];
            foreach ($patientData['billable_lines'] as $key => $item) {
                $template[] = [
                    'PatientTemplateLineItemNo' => $key + 1,
                    'PatientTemplateCPT' => $item['cpt'] ?? '',
                    'PatientTemplatePlaceOfService' => $item['pos'] ?? '',
                    'PatientTemplateModifierA' => $item['modifier_a'] ?? '',
                    'PatientTemplateModifierB' => $item['modifier_b'] ?? '',
                    'PatientTemplateModifierC' => $item['modifier_c'] ?? '',
                    'PatientTemplateModifierD' => $item['modifier_d'] ?? '',
                    'PatientTemplateDiagnosisCode' => $item['diagnose_pointer'] ?? '',
                    'PatientTemplateCharge' => $item['charge'] ?? '',
                    'PatientTemplateQuantity' => '1',
                    'PatientTemplateDescription' => '',
                ];
            }

            $payload['ctl00$phFolderContent$ucPatient$ucPATemplateLineItem$hdnJsUpdateTemplateLineItem'] = json_encode($template);
        }

        $payload['PageAction'] = 'Update';
        $payload['ctl00$phFolderContent$ucPatient$btnTriggerSave'] = '.';

        $response = $this->officeAlly->post("ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID={$patientId}", [
            'form_params' => $payload,
        ]);

        $patientData['patient_id'] = $patientId;

        $locationHeader = data_get($response->getHeader('Location'), '0');
        //check response
        if ($response->getStatusCode() === 302 && $locationHeader && preg_match('/^\/pm\/ManagePatients\/ManagePatients\.aspx/', $locationHeader)) {
            $this->log(OfficeallyLog::ACTION_UPDATE_PATIENT, true, $patientData);
        } else {
            $message = 'Patient not updated.';
            $log = $this->log(OfficeallyLog::ACTION_UPDATE_PATIENT, false, $patientData, $message);
//            $this->officeAlly->notifyIfFailed(sprintf('[Log ID: %s] %s', (string)data_get($log, 'id'), $message));
        }
    }

    /**
     * @param string      $searchQuery
     * @param             $page
     * @param string|null $viewState
     * @param string|null $viewStateGenerator
     *
     * @return mixed
     */
    public function getDiagnosisList(string $searchQuery, $page, string $viewState = null, string $viewStateGenerator = null)
    {
        if (!$viewState) {
            $response = $this->officeAlly->get("/pm/SharedFiles/popup/Popup.aspx?name=SystemDiagnosis&closeonselect=0&codeType=4", [], true);
            $response = $response->getBody()->getContents();
            $crawler = new Crawler($response);
            $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
            $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        }
        $formParams = [
            '__LASTFOCUS' => null,
            '__EVENTTARGET' => $page > 1 ? 'ctl04$popupBase$grvPopup' : '',
            '__EVENTARGUMENT' => $page > 1 ? ('Page$' . $page) : '',
            '__VIEWSTATE' => $viewState,
            '__VIEWSTATEGENERATOR' => $viewStateGenerator,
            'ctl04$popupBase$ddlSearch' => 'c.CondensedCode',
            'ctl04$popupBase$ddlCondition' => '{0}%',
            'ctl04$popupBase$txtSearch' => $searchQuery,
            'ctl04$popupBase$txtSearch2' => null,
            'ctl04$popupBase$hdnSearch2ImgState' => 'closed',
            'ctl04$popupBase$hdnShowSearch2' => 'none',
        ];
        if ($page === 1) {
            $formParams['ctl04$popupBase$btnSearch'] = 'Search';
        }
        $response = $this->officeAlly->post("/pm/SharedFiles/popup/Popup.aspx?name=SystemDiagnosis&closeonselect=0&codeType=4", [
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Referer' => 'https://pm.officeally.com/pm/SharedFiles/popup/Popup.aspx?name=SystemDiagnosis&closeonselect=0&codeType=4',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
            ],
            'form_params' => $formParams,
        ], true);

        return $response->getBody()->getContents();
    }
}
