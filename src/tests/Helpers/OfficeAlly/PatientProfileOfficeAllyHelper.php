<?php

namespace Tests\Helpers\OfficeAlly;

use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class PatientProfileOfficeAllyHelper
{
    public const OA_PATIENT_PATIENT_ID = 67304048;
    public const OA_PATIENT_FIRST_NAME = 'Abc-3';
    public const OA_PATIENT_LAST_NAME = 'Xyz-3';
    public const OA_PROVIDER_NAME = 'Name Surname, LMFT';
    public const OA_DIAGNOSE_CODE = 'F411';
    public const OA_DIAGNOSE_DESCRIPTION = 'Generalized anxiety disorder';
    public const OA_PATIENT_TEMPLATE_POSITION = 1;
    public const OA_PATIENT_TEMPLATE_CHARGE = 250.00;
    public const OA_INSURANCE_NAME = 'Aetna';
    public const OA_INSURANCE_EXTERNAL_ID = '2499199';
    public const OA_INSURANCE_PLAN_NAME = 'Medical';
    public const OA_ELIGIBILITY_PAYER_EXTERNAL_ID = '186';
    public const OA_ELIGIBILITY_PAYER_NAME = 'Kaiser Permanente of Southern California';

    public static function getPatientDataForPatientProfileHtmlMock(): array
    {
        return [
            'patient_id' => self::OA_PATIENT_PATIENT_ID,
            'last_name' => self::OA_PATIENT_LAST_NAME,
            'first_name' => self::OA_PATIENT_FIRST_NAME,
            'middle_name' => '',
            'gender' => 'M',
            'date_of_birth' => [
                'month' => 2,
                'day' => 4,
                'year' => 2017,
            ],
            'signature_on_file_date' => [
                'month' => 4,
                'day' => 1,
                'year' => 1962,
            ],
            'insurance' => [
                'name' => 'Aetna',
                'copay' => 100.00,
            ],
            'authorization' => [
                'number_of_visits_authorized' => 0,
                'number_of_visits_left' => 0,
            ],
            'cell_phone' => [
                'area_code' => '804',
                'prefix' => '406',
                'number' => '4234',
            ],
            'home_phone' => [
                'area_code' => '000',
                'prefix' => '000',
                'number' => '0000',
            ],
        ];
    }

    public static function getProviderDataForPatientProfileHtmlMock(): string
    {
        return self::OA_PROVIDER_NAME;
    }

    public static function getDiagnosisDataForPatientProfileHtmlMock(): array
    {
        return [
            1 => [
                'description' => self::OA_DIAGNOSE_DESCRIPTION,
                'code' => self::OA_DIAGNOSE_CODE,
            ],
        ];
    }

    public static function getTemplatesDataForPatientProfileHtmlMock(): string
    {
        $templates = [
            [
                "CompanyID" => 0,
                "PatientID" => 0,
                "PatientTemplatePlaceOfService" => "11",
                "PatientTemplateCPT" => "00001",
                "PatientTemplateModifierA" => "",
                "PatientTemplateModifierB" => "",
                "PatientTemplateModifierC" => "",
                "PatientTemplateModifierD" => "",
                "PatientTemplateDiagnosisCode" => "ABC",
                "PatientTemplateCharge" => self::OA_PATIENT_TEMPLATE_CHARGE,
                "PatientTemplateQuantity" => "1",
                "PatientTemplateLineItemNo" => self::OA_PATIENT_TEMPLATE_POSITION,
                "PatientTemplateDescription" => "Cash Patient - Self-Pay - Non Insured",
            ],
        ];

        return str_replace('"', '&quot;', json_encode($templates));
    }

    public static function getInsuranceDataForPatientProfileHtmlMock(): array
    {
        return  [
            'insurance' => [
                'id' => self::OA_INSURANCE_EXTERNAL_ID,
                'name' => self::OA_INSURANCE_NAME,
                'plan' => self::OA_INSURANCE_PLAN_NAME,
            ],
            'secondary_insurance' => [
                'name' => '',
                'plan' => '',
            ],
            'eligibility_payer' => [
                'id' => self::OA_ELIGIBILITY_PAYER_EXTERNAL_ID,
                'name' => self::OA_ELIGIBILITY_PAYER_NAME,
            ],
        ];
    }

    public static function getPatientHtmlForPatientProfileHtmlMock(array $patientData): string
    {
        return '
            <span id="ctl00_phFolderContent_ucPatient_lblPatientID">' . __data_get($patientData, 'patient_id') . '</span>
            <span id="ctl00_phFolderContent_ucPatient_lblLastName">' . __data_get($patientData, 'last_name') . '</span>
            <span id="ctl00_phFolderContent_ucPatient_lblFirstName">' . __data_get($patientData, 'first_name') . '</span>
            <span id="ctl00_phFolderContent_ucPatient_lblMiddleName">' . __data_get($patientData, 'middle_name') . '</span>
            <span id="ctl00_phFolderContent_ucPatient_lblGender">' . __data_get($patientData, 'gender') . '</span>
            <span id="ctl00_phFolderContent_ucPatient_DOB">
                <input value="' . __data_get($patientData, 'date_of_birth.month') . '" id="ctl00_phFolderContent_ucPatient_DOB_Month">
                <input value="' . __data_get($patientData, 'date_of_birth.day') . '" id="ctl00_phFolderContent_ucPatient_DOB_Day">;
                <input value="' . __data_get($patientData, 'date_of_birth.year') . '" id="ctl00_phFolderContent_ucPatient_DOB_Year">;
            </span>
            <span id="ctl00_phFolderContent_ucPatient_SignatureOnFileDate">
                <input value="' . __data_get($patientData, 'signature_on_file_date.month') . '" id="ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Month">
                <input value="' . __data_get($patientData, 'signature_on_file_date.day') . '" id="ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Day">
                <input value="' . __data_get($patientData, 'signature_on_file_date.year') . '" id="ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Year">
            </span>
            <span id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate">
                <input value="' . __data_get($patientData, 'authorized_start_date.month') . '" id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Month">
                <input value="' . __data_get($patientData, 'authorized_start_date.day') . '" id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Day" class="textbox">
                <input value="' . __data_get($patientData, 'authorized_start_date.year') . '" id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Year">
            </span>
            <span id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate">
                <input  value="' . __data_get($patientData, 'authorized_stop_date.month') . '" id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Month">
                <input value="' . __data_get($patientData, 'authorized_stop_date.day') . '" id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Day">
                <input value="' . __data_get($patientData, 'authorized_stop_date.year') . '" id="ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Year">
            </span>
            <input value="' . __data_get($patientData, 'insurance.subscriber_id') . '" id="ctl00_phFolderContent_ucPatient_InsuranceSubscriberID">
            <input value="' . __data_get($patientData, 'insurance.name') . '" id="ctl00_phFolderContent_ucPatient_InsuranceName">
            <input value="' . __data_get($patientData, 'insurance.copay') . '" id="ctl00_phFolderContent_ucPatient_InsuranceVisitCopay">
            <input value="' . __data_get($patientData, 'authorization.number_of_visits_authorized') . '" id="ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsAuthorized">
            <input value="' . __data_get($patientData, 'authorization.number_of_visits_left') . '" id="ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsLeft">
            <input value="' . __data_get($patientData, 'city') . '" id="ctl00_phFolderContent_ucPatient_City">
            <select id="ctl00_phFolderContent_ucPatient_lstState">
                <option selected="selected" value="' . __data_get($patientData, 'state', '') . '"></option>
            </select>
            <input value="' . __data_get($patientData, 'address_line_1') . '" id="ctl00_phFolderContent_ucPatient_AddressLine1">
            <input value="' . __data_get($patientData, 'address_line_2') . '" id="ctl00_phFolderContent_ucPatient_AddressLine2">
            <input value="' . __data_get($patientData, 'zip') . '" id="ctl00_phFolderContent_ucPatient_Zip">
            <select id="ctl00_phFolderContent_ucPatient_ddlLanguage">
                <option selected="selected" value="' . __data_get($patientData, 'language', 0) . '"></option>
            </select>
            
            <span id="ctl00_phFolderContent_ucPatient_CellPhone">
                <input value="' . __data_get($patientData, 'cell_phone.area_code') . '" id="ctl00_phFolderContent_ucPatient_CellPhone_AreaCode">
                <input value="' . __data_get($patientData, 'cell_phone.prefix') . '" id="ctl00_phFolderContent_ucPatient_CellPhone_Prefix">
                <input value="' . __data_get($patientData, 'cell_phone.number') . '" id="ctl00_phFolderContent_ucPatient_CellPhone_Number">
            </span>
            <span id="ctl00_phFolderContent_ucPatient_HomePhone">
                <input value="' . __data_get($patientData, 'home_phone.area_code') . '" id="ctl00_phFolderContent_ucPatient_HomePhone_AreaCode">
                <input value="' . __data_get($patientData, 'home_phone.prefix') . '" id="ctl00_phFolderContent_ucPatient_HomePhone_Prefix">
                <input value="' . __data_get($patientData, 'home_phone.number') . '" id="ctl00_phFolderContent_ucPatient_HomePhone_Number">
            </span>
            <span id="ctl00_phFolderContent_ucPatient_WorkPhone">
                <input value="' . __data_get($patientData, 'work_phone.area_code') . '" id="ctl00_phFolderContent_ucPatient_WorkPhone_AreaCode">
                <input value="' . __data_get($patientData, 'work_phone.prefix') . '" id="ctl00_phFolderContent_ucPatient_WorkPhone_Prefix">
                <input value="' . __data_get($patientData, 'work_phone.number') . '" id="ctl00_phFolderContent_ucPatient_WorkPhone_Number">
            </span>';
    }

    public static function getProviderHtmlForPatientProfileHtmlMock(string $providerData): string
    {
        return '<input value="' . $providerData . '" id="ctl00_phFolderContent_ucPatient_tbxPrimaryProvider">';
    }

    public static function getDiagnosisHtmlForPatientProfileHtmlMock(array $diagnosisData): string
    {
        $diagnosisHtml = '<table class="diagnosisCodes">';
        for ($i = 1; $i <= 12; $i++) {
            $diagnoseCode = __data_get($diagnosisData, $i . '.code');
            $diagnoseDescription = __data_get($diagnosisData, $i . '.description');

            $diagnosisHtml .= '
                <tr class="icd10">
                    <input id="ctl00_phFolderContent_ucPatient_ucDiagnosisCodes_dc_10_' . $i . '" value="' . $diagnoseCode . '">
                    <input id="ctl00_phFolderContent_ucPatient_ucDiagnosisCodes_dd_10_' . $i . '" value="' . $diagnoseDescription . '">
                </tr>';
        }
        $diagnosisHtml .= '</table>';

        return $diagnosisHtml;
    }

    public static function getTemplatesHtmlForPatientProfileHtmlMock(string $templatesData): string
    {
        return '<input type="hidden" id="ctl00_phFolderContent_ucPatient_ucPATemplateLineItem_hdnJsLoadTemplateLineItem" value="' . $templatesData . '">';
    }

    public static function getInsuranceHtmlForPatientProfileHtmlMock(array $insuranceData): string
    {
        return '
            <input value="' . __data_get($insuranceData, 'insurance.name') . '" id="ctl00_phFolderContent_ucPatient_InsuranceName">
            <input value="' . __data_get($insuranceData, 'insurance.id') . '" id="ctl00_phFolderContent_ucPatient_InsuranceID">
            <input value="' . __data_get($insuranceData, 'insurance.plan') . '" id="ctl00_phFolderContent_ucPatient_InsurancePlanName">
            <input value="' . __data_get($insuranceData, 'secondary_insurance.name') . '" id="ctl00_phFolderContent_ucPatient_SecondaryInsuranceName">
            <input value="' . __data_get($insuranceData, 'secondary_insurance.plan') . '" id="ctl00_phFolderContent_ucPatient_SecondaryInsurancePlanName">
            <input value="' . __data_get($insuranceData, 'eligibility_payer.id') . '" id="ctl00_phFolderContent_ucPatient_BatchEligibilityPayerID">
            <input value="' . __data_get($insuranceData, 'eligibility_payer.name') . '" id="ctl00_phFolderContent_ucPatient_BatchEligibilityPayerName">';

    }

    public static function getPatientProfileHtmlMock(array $patientData, string $providerData, array $diagnosisData, string $templatesData, array $insuranceData): string
    {

        $patientProfileHtml = '<div>';
        $patientProfileHtml .= self::getPatientHtmlForPatientProfileHtmlMock($patientData);
        $patientProfileHtml .= self::getProviderHtmlForPatientProfileHtmlMock($providerData);
        $patientProfileHtml .= self::getDiagnosisHtmlForPatientProfileHtmlMock($diagnosisData);
        $patientProfileHtml .= self::getTemplatesHtmlForPatientProfileHtmlMock($templatesData);
        $patientProfileHtml .= self::getInsuranceHtmlForPatientProfileHtmlMock($insuranceData);
        $patientProfileHtml .= '</div>';

        return $patientProfileHtml;
    }

    public static function getPatientStructureFromPatientProfile(): array
    {
        return [
            'patient_id' => 'string',
            'first_name' => 'string',
            'last_name' => 'string',
            'middle_initial' => 'string',
            'sex' => 'string',
            'date_of_birth' => 'string',
            'created_patient_date' => 'string',
            'eff_start_date' => 'string',
            'eff_stop_date' => 'string',
            'subscriber_id' => 'string',
            'primary_insurance' => 'string',
            'visit_copay' => 'string',
            'visits_auth' => 'int',
            'visits_auth_left' => 'int',
            'city' => 'string',
            'state' => 'string',
            'address' => 'string',
            'address_2' => 'string',
            'zip' => 'string',
            'cell_phone' => 'string',
            'home_phone' => 'string',
            'work_phone' => 'string',
        ];
    }

    public static function getProviderStructureFromPatientProfile(): string
    {
        return 'string';
    }

    public static function getDiagnoseStructureFromPatientProfile(): array
    {
        return [
            'code' => 'string',
            'description' => 'string',
        ];
    }

    public static function getTemplateStructureFromPatientProfile(): array
    {
        return [
            'position' => 'int',
            'pos' => 'string',
            'cpt' => 'string',
            'modifier_a' => 'string',
            'modifier_b' => 'string',
            'modifier_c' => 'string',
            'modifier_d' => 'string',
            'diagnose_pointer' => 'string',
            'charge' => 'float',
            'days_or_units' => 'int',
        ];
    }

    public static function getInsuranceStructureFromPatientProfile(): array
    {
        return [
            'insurance' => [
                'id' => 'string',
                'name' => 'string',
                'plan' => 'string',
            ],
            'secondary_insurance' => [
                'name' => 'string',
                'plan' => 'string',
            ],
            'eligibility_payer' => [
                'id' => 'string',
                'name' => 'string',
            ],
        ];
    }

    public static function getPatientDataFromPatientProfileHtml(Crawler $crawler): ?array
    {
        try {
            $patientId = $crawler->filter('#ctl00_phFolderContent_ucPatient_lblPatientID')->first()->text();
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        if (!$patientId) {
            return null;
        }

        $patientData = [
            'patient_id' => $patientId,
            'first_name' => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblFirstName')->first()->text(),
            'last_name' => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblLastName')->first()->text(),
            'middle_initial' => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblMiddleName')->first()->text(),
            'sex' => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblGender')->first()->text(),
            'date_of_birth' => self::getDateOfBirth($crawler),
            'created_patient_date' => self::getCreatedPatientDate($crawler),
            'eff_start_date' => self::getEffStartDate($crawler),
            'eff_stop_date' => self::getEffStopDate($crawler),
            'subscriber_id' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceSubscriberID')->first()->attr('value'),
            'primary_insurance' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceName')->first()->attr('value'),
            'visit_copay' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceVisitCopay')->first()->attr('value') ?? 0,
            'visits_auth' => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsAuthorized')->first()->attr('value')),
            'visits_auth_left' => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsLeft')->first()->attr('value')),
            'city' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_City')->first()->attr('value'),
            'state' => $crawler->filter('#ctl00_phFolderContent_ucPatient_lstState option:selected')->first()->attr('value'),
            'address' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine1')->first()->attr('value'),
            'address_2' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine2')->first()->attr('value'),
            'zip' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_Zip')->first()->attr('value'),

            'cell_phone' => self::getCellPhone($crawler),
            'home_phone' => self::getHomePhone($crawler),
            'work_phone' => self::getWorkPhone($crawler),
        ];

        //@todo: do research for patient languages
        //$externalLanguageId = $crawler->filter('#ctl00_phFolderContent_ucPatient_ddlLanguage option:selected')->first()->attr('value');

        return $patientData;
    }

    public static function getProviderDataFromPatientProfileHtml(Crawler $crawler): ?string
    {
        return trim(strip_tags($crawler->filter('input#ctl00_phFolderContent_ucPatient_tbxPrimaryProvider')->first()->attr('value')));
    }

    public static function getDiagnosesDataFromPatientProfileHtml(Crawler $crawler): array
    {
        $patientDiagnoses = [];

        for ($i = 1; $i <= 12; $i++) {
            $tmp = trim($crawler->filter('input#ctl00_phFolderContent_ucPatient_ucDiagnosisCodes_dd_10_' . $i)->first()->attr('value'));
            $diagnoseCode = trim($crawler->filter('input#ctl00_phFolderContent_ucPatient_ucDiagnosisCodes_dc_10_' . $i)->first()->attr('value'));

            if(!empty($tmp) && !empty($diagnoseCode) && (starts_with($diagnoseCode, 'F') || starts_with($diagnoseCode, 'Z'))) {
                $patientDiagnoses[] = [
                    'description' => $tmp,
                    'code' => $diagnoseCode,
                ];
            }
        }

        return $patientDiagnoses;
    }

    public static function getTemplatesDataFromPatientProfileHtml(Crawler $crawler): ?array
    {
        $templates = $crawler->filter('input#ctl00_phFolderContent_ucPatient_ucPATemplateLineItem_hdnJsLoadTemplateLineItem')->first()->attr('value');
        $templates = json_decode($templates, true);

        if(!$templates || !is_array($templates)) {
            return null;
        }

        $templatesData = [];

        foreach ($templates as $template) {
            $position = __data_get($template, 'PatientTemplateLineItemNo') ? (int)$template['PatientTemplateLineItemNo'] : null;
            if(empty($position)) {
                continue;
            }

            $templatesData[] = [
                'position' => $position,
                'pos' => __data_get($template, 'PatientTemplatePlaceOfService'),
                'cpt' => __data_get($template, 'PatientTemplateCPT'),
                'modifier_a' => __data_get($template, 'PatientTemplateModifierA'),
                'modifier_b' => __data_get($template, 'PatientTemplateModifierB'),
                'modifier_c' => __data_get($template, 'PatientTemplateModifierC'),
                'modifier_d' => __data_get($template, 'PatientTemplateModifierD'),
                'diagnose_pointer' => __data_get($template, 'PatientTemplateDiagnosisCode'),
                'charge' => !empty(data_get($template, 'PatientTemplateCharge')) ? (float)$template['PatientTemplateCharge'] : null,
                'days_or_units' => !empty(data_get($template, 'PatientTemplateQuantity')) ? (int)$template['PatientTemplateQuantity'] : null,
            ];
        }

        return $templatesData;
    }

    public static function getInsuranceDataFromPatientProfileHtml(Crawler $crawler): array
    {
        $insuranceName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceName')->first()->attr('value');
        $insuranceExternalId = null;
        $insurancePlanName = null;

        if ($insuranceName != '') {
            $insuranceExternalId = $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceID')->first()->attr('value') ?? null;
            $insurancePlanName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsurancePlanName')->first()->attr('value');
        }

        $secondaryInsuranceName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SecondaryInsuranceName')->first()->attr('value');
        $secondaryInsurancePlanName = null;
        if ($secondaryInsuranceName != '') {
            $secondaryInsurancePlanName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SecondaryInsurancePlanName')->first()->attr('value');
        }

        $eligibilityPayerId = $crawler->filter('input#ctl00_phFolderContent_ucPatient_BatchEligibilityPayerID')->first()->attr('value');
        $eligibilityPayerName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_BatchEligibilityPayerName')->first()->attr('value');

        return  [
            'insurance' => [
                'id' => $insuranceExternalId,
                'name' => $insuranceName,
                'plan' => $insurancePlanName,
            ],
            'secondary_insurance' => [
                'name' => $secondaryInsuranceName,
                'plan' => $secondaryInsurancePlanName,
            ],
            'eligibility_payer' => [
                'id' => $eligibilityPayerId,
                'name' => $eligibilityPayerName,
            ],
        ];
    }

    private static function getDateOfBirth(Crawler $crawler): ?string
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_DOB_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_DOB_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_DOB_Year')->first()->attr('value');

        return self::createDateFromParts($day, $month, $year);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private static function getCreatedPatientDate(Crawler $crawler): ?string
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Year')->first()->attr('value');

        return self::createDateFromParts($day, $month, $year);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private static function getEffStartDate(Crawler $crawler): ?string
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Year')->first()->attr('value');

        return self::createDateFromParts($day, $month, $year);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private static function getEffStopDate(Crawler $crawler): ?string
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Year')->first()->attr('value');

        return self::createDateFromParts($day, $month, $year);
    }

    /**
     * @param $day
     * @param $month
     * @param $year
     *
     * @return string|null
     */
    private static function createDateFromParts($day, $month, $year): ?string
    {
        $date = null;
        if (!empty($day) && !empty($month) && !empty($year)) {
            try {
                $date = Carbon::create($year, $month, $day)->toDateString();
            } catch (\InvalidArgumentException $e) {
                $date = null;
            }
        }

        return $date;
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private static function getCellPhone(Crawler $crawler): ?string
    {
        $areaCode = $crawler->filter('input#ctl00_phFolderContent_ucPatient_CellPhone_AreaCode')->first()->attr('value');
        $prefix = $crawler->filter('input#ctl00_phFolderContent_ucPatient_CellPhone_Prefix')->first()->attr('value');
        $number = $crawler->filter('input#ctl00_phFolderContent_ucPatient_CellPhone_Number')->first()->attr('value');

        return self::createPhoneFromParts($areaCode, $prefix, $number);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private static function getHomePhone(Crawler $crawler): ?string
    {
        $areaCode = $crawler->filter('input#ctl00_phFolderContent_ucPatient_HomePhone_AreaCode')->first()->attr('value');
        $prefix = $crawler->filter('input#ctl00_phFolderContent_ucPatient_HomePhone_Prefix')->first()->attr('value');
        $number = $crawler->filter('input#ctl00_phFolderContent_ucPatient_HomePhone_Number')->first()->attr('value');

        return self::createPhoneFromParts($areaCode, $prefix, $number);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private static function getWorkPhone(Crawler $crawler): ?string
    {
        $areaCode = $crawler->filter('input#ctl00_phFolderContent_ucPatient_WorkPhone_AreaCode')->first()->attr('value');
        $prefix = $crawler->filter('input#ctl00_phFolderContent_ucPatient_WorkPhone_Prefix')->first()->attr('value');
        $number = $crawler->filter('input#ctl00_phFolderContent_ucPatient_WorkPhone_Number')->first()->attr('value');

        return self::createPhoneFromParts($areaCode, $prefix, $number);
    }

    /**
     * @param $areaCode
     * @param $prefix
     * @param $number
     *
     * @return string|null
     */
    private static function createPhoneFromParts($areaCode, $prefix, $number): ?string
    {
        $phone = null;
        if (!empty($areaCode) && !empty($prefix) && !empty($number)) {
            $phone = "{$areaCode}-{$prefix}-{$number}";
        }

        return $phone;
    }
}