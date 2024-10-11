<?php

namespace App\Jobs\Parsers\Guzzle;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\EligibilityPayer;
use App\Option;
use Symfony\Component\DomCrawler\Crawler;

class EligibilityPayersParser extends AbstractParser
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
        $pageCount = $officeAllyHelper->getEligibilityPayersPageCount();
        $viewState = null;
        $viewStateGenerator = null;
        for($page = 1; $page <= $pageCount; $page++) {
            $cptCodesPage = $officeAllyHelper->getEligibilityPayers($page, $viewState, $viewStateGenerator);
            $crawler = new Crawler($cptCodesPage);
            $this->processingPayers($crawler);
            $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
            $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        }
    }
    
    /**
     * @param Crawler $crawler
     */
    private function processingPayers(Crawler $crawler)
    {
        $crawler->filter('table#ctl04_popupBase_grvPopup td script')->each(function($node) {
            $data = json_decode($node->text(), true);
            EligibilityPayer::query()->updateOrCreate([
                'external_id' => $data['PayerTaxID'],
            ], [
                'name' => trim($data['PayerName']),
            ]);
        });
    }
}
