<?php

namespace App\Helpers\Sites\OfficeAlly\Traits;

use App\Exceptions\Officeally\Appointment\VisitAlreadyExistException;
use App\Exceptions\Officeally\Appointment\VisitNotCreatedException;
use App\Exceptions\Officeally\Appointment\ClaimNotCreatedException;
use App\Exceptions\Officeally\Appointment\ClaimProviderNotUpdatedException;
use App\Models\Officeally\OfficeallyLog;
use App\Repositories\Appointment\Model\AppointmentRepositoryInterface;
use App\Appointment;
use App\Provider;
use App\BillingProvider;
use Carbon\Carbon;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Trait PatientVisits
 * @package App\Helpers\Sites\OfficeAlly\Traits
 */
trait Visits
{
    /**
     * @param $uri
     *
     * @return bool|mixed|null
     */
    private function getVisitList($uri)
    {
        $response = $this->officeAlly->get($uri, [], true);
        $visits = json_decode($response->getBody()->getContents(), true);
        if(empty($visits) && !is_null($visits)) {
            return [];
        }
        $visits = data_get($visits, 'rows');
        if($visits === null) {
            $this->officeAlly->notifyIfFailed('Visits list is not parsed.');
        }
        
        return $visits;
    }
    
    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @param array  $filters
     *
     * @return bool|mixed|null
     */
    public function getVisitListByDateRange(Carbon $startDate, Carbon $endDate, array $filters = [])
    {
        $urlFilter = [
            'rules' => array_merge($filters, [
                [
                    'field' => 'MaxRecords',
                    'data' => 100000
                ],
                [
                    'field' => 'StartDate',
                    'data' => $startDate->format('m/d/Y')
                ],
                [
                    'field' => 'EndDate',
                    'data' => $endDate->format('m/d/Y')
                ],
            ])
        ];
        $uri = 'PatientVisits/Visits.aspx?Tab=V&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&_search=false&rows=20&page=1&sidx=&sord=asc&filters2=' . urlencode(json_encode($urlFilter, JSON_UNESCAPED_SLASHES));
        
        return $this->getVisitList($uri);
    }

    public function getVisitListByPatientId(int $officeAllyPatientId)
    {
        $urlFilter = [
            'rules' => [
                [
                    'field' => 'MaxRecords',
                    'data' => 100000
                ],
                [
                    'field' => 'SearchBy',
                    'data' => 'PatientID'
                ],
                [
                    'field' => 'SearchCondition',
                    'data' => 'EqualsTo'
                ],
                [
                    'field' => 'TextField',
                    'data' => "$officeAllyPatientId"
                ],
            ]
        ];
        $uri = 'PatientVisits/Visits.aspx?Tab=V&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&_search=true&rows=20&page=1&sidx=&sord=&filters2=' . urlencode(json_encode($urlFilter, JSON_UNESCAPED_SLASHES));

        return $this->getVisitList($uri);
    }
    
    /**
     * @param $visitId
     *
     * @return bool|mixed|null
     */
    public function getVisitListById($visitId)
    {
        $urlFilter = [
            'rules' => [
                [
                    'field' => 'MaxRecords',
                    'data' => 100000
                ],
                [
                    'field' => 'SearchBy',
                    'data' => 'VisitID'
                ],
                [
                    'field' => 'SearchCondition',
                    'data' => 'StartsWith'
                ],
                [
                    'field' => 'TextField',
                    'data' => $visitId
                ]
            ]
        ];
        $uri = 'PatientVisits/Visits.aspx?Tab=V&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&_search=false&rows=20&page=1&sidx=&sord=asc&filters2=' . urlencode(json_encode($urlFilter, JSON_UNESCAPED_SLASHES));
        
        return $this->getVisitList($uri);
    }

    public function getVisitListCount($filters): int
    {
        $urlFilter = [
            'rules' => array_merge(
                [
                    [
                        'field' => 'MaxRecords',
                        'data' => 100000
                    ],
                ],
                $filters
            ),
        ];

        $uri = 'PatientVisits/Visits.aspx?Tab=V&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&_search=false&rows=20&page=1&sidx=&sord=asc&filters2=' . urlencode(json_encode($urlFilter, JSON_UNESCAPED_SLASHES));

        $response = $this->officeAlly->get($uri, [], true);
        $visits = json_decode($response->getBody()->getContents(), true);

        if(empty($visits) && !is_null($visits)) {
            return 0;
        }

        $visitsCount = data_get($visits, 'records');

        if($visitsCount === null) {
            $this->officeAlly->notifyIfFailed('Visits list count is not parsed.');
        }

        return $visitsCount ?? 0;
    }
    
    /**
     * @param $visitId
     *
     * @return mixed
     */
    public function getVisitInfo($visitId)
    {
        $response = $this->officeAlly->get("PatientVisits/EditVisit.aspx?Tab=V&PageAction=edit&ID={$visitId}&rnum=20", [], true);
    
        return $response->getBody()->getContents();
    }

    public function getVisitInfoFromAppointments($appointmentId, $patientId)
    {
        $response = $this->officeAlly->get("PatientVisits/EditVisit.aspx?Tab=V&PageAction=add&From=Appointments&AID={$appointmentId}&PID={$patientId}", [], true);
    
        return $response->getBody()->getContents();
    }
    
    /**
     * @param Appointment $appointment
     *
     * @param bool        $forceCreate
     *
     * @throws VisitAlreadyExistException
     * @throws ClaimNotCreatedException
     * @throws VisitNotCreatedException
     */
    public function createVisit(Appointment $appointment, bool $forceCreate = false, bool $allowChangeCpt = true, bool $allowChangePos = true, bool $allowChangeModifierA = true, Provider $supervisor = null)
    {
        if(!$this->isProduction()) {
            return;
        }

        //Check if visit already exist
        $dos = Carbon::createFromTimestamp($appointment->time);
        if(!$forceCreate) {
            $visits = $this->getVisitListByDateRange($dos, $dos->copy()->addDay(), [
                [
                    'field' => 'SearchBy',
                    'data' => 'PatientLastName',
                ],
                [
                    'field' => 'SearchCondition',
                    'data' => 'EqualsTo',
                ],
                [
                    'field' => 'TextField',
                    'data' => $appointment->patient->last_name,
                ],
                [
                    'field' => 'SearchByAdv',
                    'data' => 'PatientFirstName',
                ],
                [
                    'field' => 'SearchConditionAdv',
                    'data' => 'EqualsTo',
                ],
                [
                    'field' => 'TextFieldAdv',
                    'data' => $appointment->patient->first_name,
                ],
            ]);
    
            if(!empty($visits)) {
                $this->log(OfficeallyLog::ACTION_CREATE_VISIT, true, ['appointment_id' => $appointment->idAppointments], "Visit already exists.");
    
                throw new VisitAlreadyExistException($appointment);
            }
        }
        
        $page = $this->getVisitInfoFromAppointments($appointment->idAppointments, $appointment->patient->patient_id);
        $crawler = new Crawler($page);
        $payload = [];
        $crawler->filter('form#aspnetForm input, form#aspnetForm textarea')->each(function($node) use (&$payload) {
            $inputType = $node->attr('type');
            $inputName = $node->attr('name');
            if($inputType !== 'button' && $inputType !== 'submit' && !empty($inputName)) {
                $payload[$inputName] = $this->prepareValue($node->attr('value'));
            }
        });
        $crawler->filter('form#aspnetForm select')->each(function($node) use (&$payload) {
            $inputName = $node->attr('name');
            if(empty($inputName)) {
                return;
            }
            try {
                $val = $node->filter('option[selected="selected"]')->first()->attr('value');
            } catch(InvalidArgumentException $e) {
                $val = null;
            }
            
            $payload[$inputName] = $this->prepareValue($val);
        });
        $payload['__EVENTTARGET'] = null;
        $payload['__EVENTARGUMENT'] = null;
        $payload['__LASTFOCUS'] = null;
        $payload['__SCROLLPOSITIONX'] = 0;
        $payload['__SCROLLPOSITIONY'] = '295';
        $payload['ctl00$phFolderContent$ucVisitLineItem$hdnAppointmentDate'] = $dos->format('n/j/Y');
        $payload['ctl00$phFolderContent$VisitDate'] = $dos->format('n/j/Y');
        $payload['PageAction'] = 'Update';
        $insuranceName = trim(strtolower($payload['ctl00$phFolderContent$InsuranceName']));
    
        //fill billing data
        $billingProviderData = json_decode($payload['ctl00$phFolderContent$ucVisitLineItem$hdnJsLoadBillableLineItem'], true)[0][0];
        $appointmentService = app()->make(AppointmentRepositoryInterface::class);
        $correctionData = $appointmentService->visitCorrectionData(
            $appointment,
            $insuranceName,
            $billingProviderData['PlaceOfService'],
            $billingProviderData['CPT'],
            $billingProviderData['ModifierA'],
            $allowChangeCpt,
            $allowChangePos,
            $allowChangeModifierA
        );
        if(data_get($correctionData, 'change_pos')) {
            $billingProviderData['PlaceOfService'] = $correctionData['change_pos']['to'];
        }
        if(data_get($correctionData, 'change_modifier_a')) {
            $billingProviderData['ModifierA'] = $correctionData['change_modifier_a']['to'];
        }
        if(data_get($correctionData, 'change_cpt')) {
            $billingProviderData['CPT'] = $correctionData['change_cpt']['to'];
            $billingProviderData['Charge'] = $correctionData['change_cpt']['charge'];
        }
        
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$DocumentID0'] = $billingProviderData['DocumentID'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$DOS0'] = $dos->format('n/j/Y');
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$PlaceOfService0'] = $billingProviderData['PlaceOfService'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$CPT0'] = $billingProviderData['CPT'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$DiagnosisCode0'] = $billingProviderData['DiagnosisCode'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$Charge0'] = $billingProviderData['Charge'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$Quantity0'] = $billingProviderData['Quantity'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$Balance0'] = $billingProviderData['Charge'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$Description0'] = $billingProviderData['Description'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$TotalLineCharge'] = $billingProviderData['Charge'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$TotalInsurancePayment'] = '0.00';
        $payload['ctl00$phFolderContent$ucVisitLineItem$TotalPatientPayment'] = '0.00';
        $payload['ctl00$phFolderContent$ucVisitLineItem$TotalAdjustment'] = '0.00';
        $payload['ctl00$phFolderContent$ucVisitLineItem$TotalBalance'] = $billingProviderData['Charge'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$hdnDefaultPOS'] = $billingProviderData['PlaceOfService'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$ModifierA0'] = $billingProviderData['ModifierA'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$ModifierB0'] = $billingProviderData['ModifierB'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$ModifierC0'] = $billingProviderData['ModifierC'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$ucBillingCPT$ModifierD0'] = $billingProviderData['ModifierD'];
        $payload['ctl00$phFolderContent$ucVisitLineItem$hdnJsUpdateBillingLineItem'] = json_encode([
            [
                'DocumentID' => $billingProviderData['DocumentID'],
                'LineOrderNo' => (int)$billingProviderData['LineOrderNo'],
                'DOS' => $dos->format('n/j/Y'),
                'ToDOS' => $dos->format('n/j/Y'),
                'PlaceOfService' => $billingProviderData['PlaceOfService'],
                'CPT' => $billingProviderData['CPT'],
                'ModifierA' => $billingProviderData['ModifierA'],
                'ModifierB' => $billingProviderData['ModifierB'],
                'ModifierC' => $billingProviderData['ModifierC'],
                'ModifierD' => $billingProviderData['ModifierD'],
                'DiagnosisCode' => $billingProviderData['DiagnosisCode'],
                'Quantity' => $billingProviderData['Quantity'],
                'Charge' => $billingProviderData['Charge'],
                'Allowed' => $billingProviderData['Allowed'],
                'InsurancePayment' => $billingProviderData['InsurancePayment'],
                'PatientPayment' => $billingProviderData['PatientPayment'],
                'Adjustment' => $billingProviderData['Adjustment'],
                'FeeScheduleID' => $billingProviderData['FeeScheduleID'],
                'Description' => $billingProviderData['Description'],
                'START_TIME' => $billingProviderData['START_TIME'],
                'STOP_TIME' => $billingProviderData['STOP_TIME'],
                'NDCQty' => $billingProviderData['NDCQty'],
                'NDCUnitPrice' => $billingProviderData['NDCUnitPrice'],
                'NDCCODE' => $billingProviderData['NDCCODE'],
                'NDCMEASURE' => $billingProviderData['NDCMEASURE'],
                'DOS_COMMENTS' => $billingProviderData['DOS_COMMENTS'],
            ]
        ]);
        $payload['ctl00$phFolderContent$ucVisitLineItem$hdnJsUpdateNonBillingLineItem'] = '[]';
        
        //change billing provider if insurance is not kaiser or medicare
        if(!str_contains($insuranceName, 'kaiser') && !str_contains($insuranceName, 'medicare')) {
            $billingProvider = BillingProvider::findOrFail(190472);
            $payload['ctl00$phFolderContent$hdnBillingProviderID'] = $billingProvider->id;
            $payload['ctl00$phFolderContent$BillingProviderProviderID'] = $billingProvider->id;
            $payload['ctl00$phFolderContent$hdnBillingProviderName'] = $billingProvider->name;
            $payload['ctl00$phFolderContent$hdnBillingProviderAddress'] = $billingProvider->address;
            $payload['ctl00$phFolderContent$hdnBillingProviderCity'] = $billingProvider->city;
            $payload['ctl00$phFolderContent$hdnBillingProviderState'] = $billingProvider->state;
            $payload['ctl00$phFolderContent$hdnBillingProviderZip'] = $billingProvider->zip;
            $payload['ctl00$phFolderContent$hdnBillingProviderPhone'] = $billingProvider->phone;
            $payload['ctl00$phFolderContent$hdnBillingProviderTaxID'] = $billingProvider->tax_id;
            $payload['ctl00$phFolderContent$hdnBillingProviderNPI'] = $billingProvider->npi;
        }
        if(str_contains($insuranceName, 'beacon')) {
            $payload['ctl00$phFolderContent$VisitFacilityID'] = 229906;
            $payload['ctl00$phFolderContent$hdnVisitFacilityID'] = 229906;
            $payload['ctl00$phFolderContent$FacilityProviderID'] = 229906;
            $payload['ctl00$phFolderContent$hdnFacilityName'] = 'Change Within Reach Inc.';
            $payload['ctl00$phFolderContent$hdnFacilityAddress'] = '17777 Ventura Blvd., Suite105';
            $payload['ctl00$phFolderContent$hdnFacilityCity'] = 'Encino';
            $payload['ctl00$phFolderContent$hdnFacilityState'] = 'CA';
            $payload['ctl00$phFolderContent$hdnFacilityZip'] = '91316';
            $payload['ctl00$phFolderContent$hdnFacilityNPI'] = '';
        }
        $payload['ctl00$phFolderContent$btnTriggerSave'] = '.';
      
        $response = $this->officeAlly->post("PatientVisits/EditVisit.aspx?Tab=V&PageAction=add&From=Appointments&AID={$appointment->idAppointments}&PID={$appointment->patient->patient_id}", [
            'form_params' => $payload,
        ], true, false);

        $location = (string)data_get($response->getHeader('Location'), '0');
        $locationParts = [];
        parse_str($location, $locationParts);
        if(empty(data_get($locationParts, 'NVID'))) {
            $log = $this->log(OfficeallyLog::ACTION_CREATE_VISIT, false, ['appointment_id' => $appointment->idAppointments]);
            $exception = new VisitNotCreatedException($appointment->idAppointments);
            $this->officeAlly->notifyIfFailed(sprintf('[Log ID: %s] %s', (string)data_get($log, 'id'), $exception->getMessage()));
            
            throw $exception;
        }
        $this->log(OfficeallyLog::ACTION_CREATE_VISIT, true, ['appointment_id' => $appointment->idAppointments, 'visit_id' => $locationParts['NVID']]);

        if (!$appointment->patient->is_self_pay) {
            $this->createClaim($locationParts['NVID'], $supervisor);
        }
    }
    
    /**
     * @param $visitId
     *
     * @throws ClaimNotCreatedException
     */
    public function createClaim($visitId, Provider $supervisor = null)
    {
        if (!$this->isProduction()) {
            return;
        }

        $response = $this->officeAlly->post('PatientVisits/Api.aspx?oper=CreateHCFAClaimBatch0212', [
            'headers' => [
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
            ],
            'json' => [
                'id' => [
                    (string)$visitId,
                ],
                '__RequestVerificationToken' => $this->getRequestVerificationToken(),
            ]
        ], true)->getBody()->getContents();
        $response = json_decode($response, true);
        $responseMessage = data_get($response, 'Message') ?? '';
        $matches = [];
        preg_match("/{$visitId}.*Claim (?<claim_num>\d+) created successfully/", $responseMessage, $matches);
        $claimNumber = $matches['claim_num'] ?? null;

        if (empty($claimNumber)) {
            $this->log(OfficeallyLog::ACTION_CREATE_CLAIM, false, ['visit_id' => $visitId], $responseMessage);
            throw new ClaimNotCreatedException($responseMessage);
        }

        $this->log(OfficeallyLog::ACTION_CREATE_CLAIM, true, ['visit_id' => $visitId, 'claim_number' => $claimNumber], $responseMessage);

        if (empty($supervisor)) {
            return;
        }

        $this->updateProviderInClaim($claimNumber, $supervisor);
    }

    public function getClaimInfo($claimNumber)
    {
        $response = $this->officeAlly->get("Claims/EditClaim_HCFARefactor.aspx?Tab=B&PageAction=edit&ClaimNo={$claimNumber}&ClaimType=user&From=ClaimList", [], true);
    
        return $response->getBody()->getContents();
    }

    public function getDiagnosesFromClaim($claimNumber): array
    {
        $page = $this->getClaimInfo($claimNumber);
        $crawler = new Crawler($page);

        $diagnoses = $crawler->filter('#MainFolder input.diag-autocomplete')->each(function ($node) {
            if (str_starts_with($node->attr('id'), 'ctl00_phFolderContent_ucHCFA_DIAGNOSIS_CODECMS')) {
                return $node->attr('value');
            }
            return null;
        });
        return array_unique(array_values(array_filter($diagnoses, function ($diagnose) {
            return $diagnose !== null;
        })));
    }

    public function getCptCodeFromClaim($claimNumber): ?string
    {
        $page = $this->getClaimInfo($claimNumber);
        $crawler = new Crawler($page);

        try {
            $item = $crawler->filter('#ctl00_phFolderContent_ucHCFA_ucHCFALineItem_hdnLoadHCFALineItem')
                ->first();

            return data_get(json_decode($item->attr('value')), '0.0.CPT_CODE');
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function updateProviderInClaim($claimNumber, Provider $provider)
    {
        $page = $this->getClaimInfo($claimNumber);
        $crawler = new Crawler($page);
        $payload = [];
        $crawler->filter('form#aspnetForm input, form#aspnetForm textarea')->each(function($node) use (&$payload) {
            $inputType = $node->attr('type');
            $inputName = $node->attr('name');
            if($inputType === 'button' || $inputType === 'submit' || empty($inputName)) {
                return;
            }
            if(($inputType === 'checkbox' || $inputType === 'radio') && $node->attr('checked') !== 'checked') {
                return;
            }

            $payload[$inputName] = $this->prepareValue($node->attr('value'));
        });
        $crawler->filter('form#aspnetForm select')->each(function($node) use (&$payload) {
            $inputName = $node->attr('name');
            if(empty($inputName)) {
                return;
            }
            try {
                $val = $node->filter('option[selected="selected"]')->first()->attr('value');
            } catch(InvalidArgumentException $e) {
                $val = null;
            }
            
            $payload[$inputName] = $this->prepareValue($val);
        });
        
        $payload['__EVENTTARGET'] = 'ctl00$phFolderContent$ucHCFA$btnPMUpdate';
        $payload['__EVENTARGUMENT'] = 'OnClick';
        $payload['__SCROLLPOSITIONX'] = '0';
        $payload['__SCROLLPOSITIONY'] = '0';

        $payload['ctl00$phFolderContent$ucHCFA$SUPPLIER_2_LAST'] = $provider->last_name;
        $payload['ctl00$phFolderContent$ucHCFA$SUPPLIER_2_FIRST'] = $provider->first_name;
        $payload['ctl00$phFolderContent$ucHCFA$SUPPLIER_2_MI'] = $provider->middle_initial;
        $payload['ctl00$phFolderContent$ucHCFA$RENDERING_PHYSICIAN_TAXONOMY'] = $provider->taxonomy_code;
        $payload['ctl00$phFolderContent$ucHCFA$PRACTICE_ID'] = $provider->license_no;
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$RENDERING_PHYSICIAN_ID0'] = $provider->license_no; 
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$RENDERING_PHYSICIAN_NPI0'] = $provider->individual_npi;

        $claimData = json_decode($payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$hdnLoadHCFALineItem'], true)[0][0];
        
        $claimData['RENDERING_PHYSICIAN_TAXONOMY'] = $provider->taxonomy_code;
        $claimData['RENDERING_PHYSICIAN_ID'] = $provider->license_no;
        $claimData['RENDERING_PHYSICIAN_NPI'] = $provider->individual_npi;

        $fromDate = explode('/', $claimData['FM_DATE_OF_SVC']);
        if (count($fromDate) > 2) {
            $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$FM_DATE_OF_SVC_MONTH0'] = $fromDate[0];
            $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$FM_DATE_OF_SVC_DAY0'] = $fromDate[1];
            $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$FM_DATE_OF_SVC_YEAR0'] = $fromDate[2];
        }
        $toDate = explode('/', $claimData['TO_DATE_OF_SVC']);
        if (count($toDate) > 2) {
            $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$TO_DATE_OF_SVC_MONTH0'] = $toDate[0];
            $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$TO_DATE_OF_SVC_DAY0'] = $toDate[1];
            $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$TO_DATE_OF_SVC_YEAR0'] = $toDate[2];
        }
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$PLACE_OF_SVC0'] = $claimData['PLACE_OF_SVC'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$EMG0'] = $claimData['EMG'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$CPT_CODE0'] = $claimData['CPT_CODE'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$MODIFIER_A0'] = $claimData['MODIFIER_A'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$MODIFIER_B0'] = $claimData['MODIFIER_B'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$MODIFIER_C0'] = $claimData['MODIFIER_C'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$MODIFIER_D0'] = $claimData['MODIFIER_D'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$DOS_DIAG_CODE0'] = $claimData['DOS_DIAG_CODE'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$UNITS0'] = $claimData['UNITS'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$DOS_CHRG0'] = $claimData['DOS_CHRG'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$EPSDT_FAMILY_PLAN0'] = $claimData['EPSDT_FAMILY_PLAN'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$RENDERING_PHYSICIAN_NPI0'] = $claimData['RENDERING_PHYSICIAN_NPI'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$DOS_COMMENTS0'] = $claimData['DOS_COMMENTS'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$START_TIME0'] = $claimData['START_TIME'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$STOP_TIME0'] = $claimData['STOP_TIME'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$DRUG_QUALIFIER0'] = $claimData['DRUG_QUALIFIER'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$NDCCODE0'] = $claimData['NDCCODE'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$NDCMEASURE0'] = $claimData['NDCMEASURE'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$NDCUNITS0'] = $claimData['NDCUNITS'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$NDCCHARGE0'] = $claimData['NDCCHARGE'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$RENDERING_PHYSICIAN_QUALIFIER0'] = $claimData['RENDERING_PHYSICIAN_QUALIFIER'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$RENDERING_PHYSICIAN_ID0'] = $claimData['RENDERING_PHYSICIAN_ID'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$TYPE_OF_SVC0'] = $claimData['TYPE_OF_SVC'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$COB0'] = $claimData['COB'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$DOS_RESERVED0'] = $claimData['DOS_RESERVED'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$ucClaimLineItem$VisitLineItemID0'] = $claimData['VisitLineItemID'];
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$hdnJsUpdateHCFALineItem'] = json_encode([
            [
                "LINE_ITEM_ID" => 1,
                "FM_DATE_OF_SVC" => str_replace("/", "-", $claimData['FM_DATE_OF_SVC']),
                "TO_DATE_OF_SVC" => str_replace("/", "-", $claimData['TO_DATE_OF_SVC']),
                "PLACE_OF_SVC" => $this->removeSpaces($claimData['PLACE_OF_SVC']),
                "EMG" => $this->removeSpaces($claimData['EMG']),
                "CPT_CODE" => $this->removeSpaces($claimData['CPT_CODE']),
                "MODIFIER_A" => $this->removeSpaces($claimData['MODIFIER_A']),
                "MODIFIER_B" => $this->removeSpaces($claimData['MODIFIER_B']),
                "MODIFIER_C" => $this->removeSpaces($claimData['MODIFIER_C']),
                "MODIFIER_D" => $this->removeSpaces($claimData['MODIFIER_D']),
                "DOS_DIAG_CODE" => $this->removeSpaces($claimData['DOS_DIAG_CODE']),
                "UNITS" => $this->removeSpaces($claimData['UNITS']),
                "DOS_CHRG" => $this->removeSpaces($claimData['DOS_CHRG']),
                "EPSDT_FAMILY_PLAN" => $this->removeSpaces($claimData['EPSDT_FAMILY_PLAN']),
                "RENDERING_PHYSICIAN_NPI" => $this->removeSpaces($claimData['RENDERING_PHYSICIAN_NPI']),
                "Original_LINE_ITEM_ID" => "",
                "DOS_COMMENTS" => $this->removeSpaces($claimData['DOS_COMMENTS']),
                "START_TIME" => $this->removeSpaces($claimData['START_TIME']),
                "STOP_TIME" => $this->removeSpaces($claimData['STOP_TIME']),
                "DRUG_QUALIFIER" => $this->removeSpaces($claimData['DRUG_QUALIFIER']),
                "NDCCODE" => $this->removeSpaces($claimData['NDCCODE']),
                "NDCMEASURE" => $this->removeSpaces($claimData['NDCMEASURE']),
                "NDCUNITS" => $this->removeSpaces($claimData['NDCUNITS']),
                "NDCCHARGE" => $this->removeSpaces($claimData['NDCCHARGE']),
                "RENDERING_PHYSICIAN_QUALIFIER" => $this->removeSpaces($claimData['RENDERING_PHYSICIAN_QUALIFIER']),
                "RENDERING_PHYSICIAN_ID" => $this->removeSpaces($claimData['RENDERING_PHYSICIAN_ID']),
                "nUNITS" => $this->removeSpaces($claimData['UNITS']),
                "TYPE_OF_SVC" => $this->removeSpaces($claimData['TYPE_OF_SVC']),
                "COB" => $this->removeSpaces($claimData['COB']),
                "DOS_RESERVED" => $this->removeSpaces($claimData['DOS_RESERVED']),
                "VisitLineItemID" => $this->removeSpaces($claimData['VisitLineItemID'])
            ]
        ]);

        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$hdnJsUpdateSecondaryHCFALineItem'] = '[]';
        $payload['ctl00$phFolderContent$ucHCFA$ucHCFALineItem$hdnUpdateGroupCode'] = '[]';

        $payload['ctl00$phFolderContent$ucHCFA$ucSecondaryLineItem$hdnJsUpdateSecondaryHCFALineItem'] = '[]';
        $payload['ctl00$phFolderContent$ucHCFA$ucSecondaryLineItem$hdnUpdateGroupCode'] = '[]';

        $response = $this->officeAlly->post("Claims/EditClaim_HCFARefactor.aspx?Tab=B&PageAction=edit&ClaimNo={$claimNumber}&ClaimType=user&From=ClaimList", [
            'form_params' => $payload,
        ], true, false);

        $location = (string)data_get($response->getHeader('Location'), '0');
        $locationParts = [];
        parse_str($location, $locationParts);
        
        if (count($locationParts)) {
            $this->log(OfficeallyLog::ACTION_UPDATE_PROVIDER_IN_CLAIM, true, ['claim_number' => $claimNumber, 'provider_id' => $provider->officeally_id]);
        } else {
            $this->log(OfficeallyLog::ACTION_UPDATE_PROVIDER_IN_CLAIM, false, ['claim_number' => $claimNumber, 'provider_id' => $provider->officeally_id]);
            throw new ClaimProviderNotUpdatedException($claimNumber, $provider);
        }
    }
}