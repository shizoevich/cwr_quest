<?php

namespace App\Jobs\Parsers\Guzzle;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use App\BillingProvider;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

class BillingProvidersParser extends AbstractParser
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_1);
        $billingProvidersPage = $officeAllyHelper->getBillingProviders();
        
        $billingProviderIds = $this->billingProvidersCrawler($billingProvidersPage);

        if (!count($billingProviderIds)) {
            with(new ExceptionNotificator())->officeAllyNotifyAndSendToSentry(new AnErrorOccurred('Billing Provider list is not parsed.'));
        }
    }

    private function billingProvidersCrawler($billingProvidersPage)
    {
        $billingProviderIds = [];

        $crawler = new Crawler($billingProvidersPage);
        $crawler->filter('table#ctl04_popupBase_grvPopup tr:not(.textbold)')->each(function($node) use (&$billingProviderIds) {
            $id = $node->filter('td:first-of-type script')->text();
            if (!$id) {
                return;
            }

            $id = explode('|', $id);
            if (!key_exists(10, $id)) {
                return;
            }

            $phone = '';
            if (key_exists(5, $id)) {
                $phone = $id[5];
            }
            
            $id = $id[10];
            $name = $node->filter('td')->eq(1)->text();
            $address = $node->filter('td')->eq(2)->text();
            $city = $node->filter('td')->eq(3)->text();
            $state = $node->filter('td')->eq(4)->text();
            $zip = $node->filter('td')->eq(5)->text();
            $group_no = $node->filter('td')->eq(6)->text();
            $tax_id = $node->filter('td')->eq(7)->text();
            $npi = $node->filter('td')->eq(8)->text();
        
            if (!is_null($name) && !is_null($address) && !is_null($city) && !is_null($state) && !is_null($zip)) {
                $data = compact('id', 'name', 'address', 'city', 'state', 'zip', 'group_no', 'tax_id', 'npi', 'phone');
                BillingProvider::updateOrCreate(['id' => $id], $data);
                $billingProviderIds[] = $id;
            }
        });

        return $billingProviderIds;
    }
}
