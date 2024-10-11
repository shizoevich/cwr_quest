<?php

namespace Tests\Helpers\OfficeAlly;

use Carbon\Carbon;
use Psy\Exception\BreakException;
use Symfony\Component\DomCrawler\Crawler;

class DiagnosesOfficeAllyHelper
{
    public static function getDiagnosisDataFromHtml($diagnosisHtml)
    {
        $crawler = new Crawler($diagnosisHtml);

        $diagnosisData = null;

        try {
            $crawler->filter('table#ctl04_popupBase_grvPopup td script')->each(function($node) use (&$diagnosisData){
                $tempData = json_decode($node->text(), true);

                $diagnosisData = [
                    'code' => trim($tempData['Code']),
                    'description' => trim($tempData['Description']),
                    'hcc' => trim($tempData['HCC']) === 'Y' ? 1 : 0,
                    'is_billable' => trim($tempData['Billable']) === 'Yes' ? 1 : 0,
                    'terminated_at' => trim($tempData['TerminatedDate']) ? Carbon::parse($tempData['TerminatedDate'])->toDateString() : null
                ];

                throw new BreakException();
            });
        } catch (BreakException $e) {
            return $diagnosisData;
        }
        return $diagnosisData;
    }

    public static function getStructureDiagnosisData(): array
    {
        return [
            'code' => 'string',
            'description' => 'string',
            'hcc' => 'int',
            'terminated_at' => 'string',
            'is_billable' => 'int',
        ];
    }

    public static function getDiagnosisDataForHtml(): array
    {
        return [
            'code' => 'F0150',
            'description' => 'Vascular dementia, unspecified severity, without behavioral disturbance, psychotic disturbance, mood disturbance, and anxiety',
            'hcc' => 1,
            'terminated_at' => null,
            'is_billable' => 1,
            'condensed_code' => 'F0150'
        ];
    }

    public static function mockGetDiagnosisListHtml(array $diagnosisData): string
    {
        $hcc = $diagnosisData['hcc'] === 1 ? 'Y' : 'Z';
        $billiable = $diagnosisData['is_billable'] === 1 ? 'Yes' : 'No';

        return '<div>
            <input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="" />
            <input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="" />
            <table id="ctl04_popupBase_grvPopup">
                <tr>
                    <td>
                        <script type="text/x-oa-template">
                            {
                                "Code":"' . $diagnosisData['code'] . '",
                                "Description":"' . $diagnosisData['description'] . '",
                                "HCC":"' . $hcc . '",
                                "TerminatedDate":"' . $diagnosisData['terminated_at'] . '",
                                "Billable":"' . $billiable . '",
                                "CondensedCode":"' . $diagnosisData['condensed_code'] . '"
                            }
                        </script>
                    </td>
                </tr>
            </table>
        </div>';
    }
}