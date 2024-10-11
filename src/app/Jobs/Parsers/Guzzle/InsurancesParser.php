<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\InsurancesDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use App\PatientInsurance;
use Symfony\Component\DomCrawler\Crawler;

class InsurancesParser extends AbstractParser
{
    use Pagination;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_1);
        $insurancesPage = $officeAllyHelper->getInsurances();
        $crawler = new Crawler($insurancesPage);
        $this->processingInsurances($crawler);
        $pageCount = $this->getPagesCountWithChangedXPath($crawler);
        $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
        $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        for($page = 2; $page <= $pageCount; $page++) {
            $insurancesPage = $officeAllyHelper->getInsurances($page, $viewState, $viewStateGenerator);
            $crawler = new Crawler($insurancesPage);
            $this->processingInsurances($crawler);
            $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
            $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        }
    }
    
    /**
     * @param Crawler $crawler
     */
    private function processingInsurances(Crawler $crawler)
    {
        $crawler->filter('table#ctl04_popupBase_grvPopup td script')->each(function($node) {
            $insuranceData = json_decode($node->text(), true);

            // PatientInsurance::query()->updateOrCreate([
            //     'external_id' => $insuranceData['InsuranceID'], 
            // ], [
            //     'insurance' => html_entity_decode($insuranceData['InsuranceName']),
            //     'address_line_1' => !empty($insuranceData['AddressLine1']) ? $insuranceData['AddressLine1'] : null,
            //     'city' => !empty($insuranceData['City']) ? $insuranceData['City'] : null,
            //     'state' => !empty($insuranceData['State']) ? $insuranceData['State'] : null,
            //     'zip' => !empty($insuranceData['Zip']) ? $insuranceData['Zip'] : null,
            // ]);

            $insuranceDTO = new InsurancesDTO([
                'external_id' => $insuranceData['InsuranceID'], 
                'insurance' => html_entity_decode($insuranceData['InsuranceName']),
                'address_line_1' => !empty($insuranceData['AddressLine1']) ? $insuranceData['AddressLine1'] : null,
                'city' => !empty($insuranceData['City']) ? $insuranceData['City'] : null,
                'state' => !empty($insuranceData['State']) ? $insuranceData['State'] : null,
                'zip' => !empty($insuranceData['Zip']) ? $insuranceData['Zip'] : null,
            ]);

            PatientInsurance::query()->updateOrCreate([
                'external_id' => $insuranceDTO->external_id, 
            ], [
                'insurance' => $insuranceDTO->insurance, 
                'address_line_1' => $insuranceDTO->address_line_1, 
                'city' => $insuranceDTO->city, 
                'state' => $insuranceDTO->state, 
                'zip' => $insuranceDTO->zip, 
            ]);
        });
    }

}
