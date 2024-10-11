<?php

namespace Tests\Helpers\OfficeAlly;

use Psy\Exception\BreakException;
use Symfony\Component\DomCrawler\Crawler;

class InsurancesOfficeAllyHelper
{
    public static function getInsuranceDataFromHtml($insurancesHtml)
    {
        $crawler = new Crawler($insurancesHtml);

        $insuranceData = null;

        try {
            $crawler->filter('table#ctl04_popupBase_grvPopup td script')->each(function($node) use (&$insuranceData){
                $tempData = json_decode($node->text(), true);

                $insuranceData = [
                    'external_id' => $tempData['InsuranceID'],
                    'insurance' => html_entity_decode($tempData['InsuranceName']),
                    'address_line_1' => !empty($tempData['AddressLine1']) ? $tempData['AddressLine1'] : null,
                    'city' => !empty($tempData['City']) ? $tempData['City'] : null,
                    'state' => !empty($tempData['State']) ? $tempData['State'] : null,
                    'zip' => !empty($tempData['Zip']) ? $tempData['Zip'] : null,
                ];

                throw new BreakException();
            });
        } catch (BreakException $e) {
            //
        }

        return $insuranceData;
    }

    public static function getStructureInsuranceData(): array
    {
        return [
            'external_id' => 'string',
            'insurance' => 'string',
            'address_line_1' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string',
        ];
    }

    public static function getInsuranceDataForHtml(): array
    {
        return [
            'external_id' => '1888463',
            'insurance' => 'Kaiser Permanente',
            'address_line_1' => 'Kaiser Foundation Health Plan,Inc',
            'city' => 'Downey',
            'state' => 'CA',
            'zip' => '90242-7004',
        ];
    }

    public static function mockGetInsurancesHtml(array $insuranceData): string
    {
        return '<div>
            <input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="">
            <input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="">
            <table id="ctl04_popupBase_grvPopup">
                <tr>
                    <td>
                        <script type="text/x-oa-template">
                            {
                                "InsuranceID":"' . $insuranceData['external_id'] . '",
                                "InsuranceName":"' . $insuranceData['insurance'] . '",
                                "AddressLine1":"' . $insuranceData['address_line_1'] . '",
                                "City":"' . $insuranceData['city'] . '",
                                "State":"' . $insuranceData['state'] . '",
                                "Zip":"' . $insuranceData['zip'] . '"
                            }
                        </script>
                    </td>
                </tr>
            </table>
        </div>';
    }
}