<?php

namespace Tests\Helpers\OfficeAlly;

use Symfony\Component\DomCrawler\Crawler;

class VisitInfoOfficeAllyHelper
{
    public static function getVisitInfoDataFromHtml($html)
    {
        $crawler = new Crawler($html);

        return [
            'patient_id' => $crawler->filter('input#ctl00_phFolderContent_PatientID')->first()->attr('value'),
            'copay' => $crawler->filter('input#ctl00_phFolderContent_InsuranceVisitCopay')->first()->attr('value'),
            'provider_id' => $crawler->filter('input#ctl00_phFolderContent_ProviderID')->first()->attr('value'),
            'visit_reason' => $crawler->filter('input#ctl00_phFolderContent_ReasonForVisit')->first()->attr('value'),
            'is_cash' => (int)($crawler->filter('select#ctl00_phFolderContent_lstStatus  option:selected')->first()->text() === 'Cash Payment'),
            'diagnose_code' => trim($crawler->filter('#ctl00_phFolderContent_ucDiagnosisCodes_dc_10_' . 1)->first()->attr('value')),
            'diagnose_description' => trim($crawler->filter('#ctl00_phFolderContent_ucDiagnosisCodes_dd_10_' . 1)->first()->attr('value')),
            'insurance_name' => $crawler->filter('input#ctl00_phFolderContent_InsuranceName')->first()->attr('value'),
            'insurance_plan_name' => $crawler->filter('input#ctl00_phFolderContent_InsurancePlanName')->first()->attr('value'),
            'billings' => $crawler->filter('#ctl00_phFolderContent_ucVisitLineItem_hdnJsLoadBillableLineItem')->getNode(0)
        ];
    }

    public static function getStructureVisitInfoData(): array
    {
        return [
            "patient_id" => "string",
            "copay" => "string",
            "provider_id" => "string",
            "visit_reason" => "string",
            "is_cash" => 'int',
            'diagnose_code' => 'string',
            'diagnose_description' => 'string',
            'insurance_name' => 'string',
            'insurance_plan_name' => 'string',
            'billings' => [
                'CPT' => 'string',
                'Description' => 'string'
            ]
        ];
    }

    public static function getBillingsFromCell($cell)
    {
        $billings = $cell->getAttribute('value');
        $billings = htmlspecialchars_decode($billings);
        $billings = json_decode($billings, true);
        $billings = data_get($billings, '0.0');

        return $billings;
    }

    public static function getVisitInfoDataForHtml()
    {
        return [
            'patient_id' => '123456',
            'copay' => 'Test Text',
            'provider_id' => '123456',
            'visit_reason' => 'Test Reason',
            'is_cash' => 1,
            'payment' => 'Cash Payment',
            'diagnose_code' => '123456',
            'diagnose_description' => 'Test Diagnose Description',
            'insurance_name' => 'Test Insurance Name',
            'insurance_plan_name' => 'Test Insurance Plan Name',
            'billing_cpt' => '12345',
            'billing_description' => 'Test Billing Description',
            'billing_place_of_service' => 'Test Plase of Service'
        ];
    }

    public static function getMockVisitInfoHtml($visitInfoData)
    {
        $diagnoseCodes = '<input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_1" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_2" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_3" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_4" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_5" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_6" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_7" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_8" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_9" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_10" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_11" value="' . $visitInfoData['diagnose_code'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dc_10_12" value="' . $visitInfoData['diagnose_code'] . '" />';
        $diagnoseDescriptions = '<input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_1" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_2" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_3" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_4" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_5" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_6" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_7" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_8" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_9" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_10" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_11" value="' . $visitInfoData['diagnose_description'] . '" />
            <input id="ctl00_phFolderContent_ucDiagnosisCodes_dd_10_12" value="' . $visitInfoData['diagnose_description'] . '" />';

        return '<div>
            <input value="' . $visitInfoData['patient_id'] . '" id="ctl00_phFolderContent_PatientID" />
            <input value="' . $visitInfoData['copay'] . '" id="ctl00_phFolderContent_InsuranceVisitCopay" />
            <input value="' . $visitInfoData['provider_id'] . '" id="ctl00_phFolderContent_ProviderID" />
            <input value="' . $visitInfoData['visit_reason'] . '" id="ctl00_phFolderContent_ReasonForVisit" />
            <select id="ctl00_phFolderContent_lstStatus">
                <option selected="selected">' . $visitInfoData['payment'] . '</option>
            </select>
            <input value="' . $visitInfoData['insurance_name'] . '" id="ctl00_phFolderContent_InsuranceName" />
            <input value="' . $visitInfoData['insurance_plan_name'] . '" id="ctl00_phFolderContent_InsurancePlanName" />
            <input type="hidden" id="ctl00_phFolderContent_ucVisitLineItem_hdnJsLoadBillableLineItem" value="[[{&quot;CPT&quot;:&quot;' . $visitInfoData['billing_cpt'] . '&quot;,&quot;Description&quot;:&quot;' . $visitInfoData['billing_description'] . '&quot;,&quot;PlaceOfService&quot;:&quot;' . $visitInfoData['billing_place_of_service'] . '&quot;}]]" />
            ' . $diagnoseCodes . $diagnoseDescriptions . '
        </div>';
    }
}
