<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\CPTCodesDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use App\PatientInsuranceProcedure;
use Symfony\Component\DomCrawler\Crawler;

class CPTCodesParser extends AbstractParser
{
    use Pagination;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_1);
        $cptCodesPage = $officeAllyHelper->getCPTCodes();
        $crawler = new Crawler($cptCodesPage);
        $this->processingCPTCodes($crawler);
        $pageCount = $this->getPagesCount($crawler);
        $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
        $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        for($page = 2; $page <= $pageCount; $page++) {
            $cptCodesPage = $officeAllyHelper->getCPTCodes($page, $viewState, $viewStateGenerator);
            $crawler = new Crawler($cptCodesPage);
            $this->processingCPTCodes($crawler);
            $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
            $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        }
    }
    
    /**
     * @param Crawler $crawler
     */
    private function processingCPTCodes(Crawler $crawler)
    {
        $crawler->filter('table#ctl04_popupBase_grvPopup td script')->each(function($node) {
            $cptDataJsonDecode = json_decode($node->text(), true);

            $cptDataDTO = new CPTCodesDTO([
                'code' => $cptDataJsonDecode['Code'],
                'name' => $cptDataJsonDecode['Description'],
                'pos' => !empty($cptDataJsonDecode['POS']) ? $cptDataJsonDecode['POS'] : null,
                'modifier_a' => !empty($cptDataJsonDecode['Mod_A']) ? $cptDataJsonDecode['Mod_A'] : null,
                'modifier_b' => !empty($cptDataJsonDecode['Mod_B']) ? $cptDataJsonDecode['Mod_B'] : null,
                'modifier_c' => !empty($cptDataJsonDecode['Mod_C']) ? $cptDataJsonDecode['Mod_C'] : null,
                'modifier_d' => !empty($cptDataJsonDecode['Mod_D']) ? $cptDataJsonDecode['Mod_D'] : null,
                'charge' => !empty($cptDataJsonDecode['ChargeA']) ? $cptDataJsonDecode['ChargeA'] : null,
            ]);

            PatientInsuranceProcedure::query()->updateOrCreate([
                'code' => $cptDataDTO->code,
            ], [
                'name' => $cptDataDTO->name,
                'pos' => $cptDataDTO->pos,
                'modifier_a' => $cptDataDTO->modifier_a,
                'modifier_b' => $cptDataDTO->modifier_b,
                'modifier_c' => $cptDataDTO->modifier_c,
                'modifier_d' => $cptDataDTO->modifier_d,
                'charge' => $cptDataDTO->charge,
            ]);
        });
    }
}
