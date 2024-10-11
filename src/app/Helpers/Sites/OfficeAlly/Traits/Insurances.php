<?php

namespace App\Helpers\Sites\OfficeAlly\Traits;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Trait Appointments
 * @package App\Helpers\Sites\OfficeAlly\Traits
 */
trait Insurances
{
    /**
     * @param int  $page
     * @param null $viewState
     * @param null $viewStateGenerator
     *
     * @return mixed
     */
    public function getInsurances(int $page = 1, $viewState = null, $viewStateGenerator = null)
    {
        $uri = 'SharedFiles/popup/Popup.aspx?name=Insurance';
        if($page === 1) {
            $response = $this->officeAlly->get($uri, [], true);
        } else {
            if(!$viewState || !$viewStateGenerator) {
                throw new \RuntimeException('__VIEWSTATE and __VIEWSTATEGENERATOR are required.');
            }
            $response = $this->officeAlly->post($uri, [
                'form_params' => [
                    '__LASTFOCUS' => null,
                    '__EVENTTARGET' => $page > 1 ? 'ctl04$popupBase$grvPopup' : '',
                    '__EVENTARGUMENT' => $page > 1 ? ('Page$' . $page) : '',
                    '__VIEWSTATE' => $viewState,
                    '__VIEWSTATEGENERATOR' => $viewStateGenerator,
                    'ctl04$popupBase$ddlSearch' => 'i.InsuranceName',
                    'ctl04$popupBase$ddlCondition' => '{0}%',
                    'ctl04$popupBase$txtSearch' => '',
                    'ctl04$popupBase$txtSearch2' => '',
                    'ctl04$popupBase$hdnSearch2ImgState' => 'closed',
                    'ctl04$popupBase$hdnShowSearch2' => 'none',
                ],
            ], true);
        }
        
        return $response->getBody()->getContents();
    }
    
    /**
     * @param int  $page
     * @param null $viewState
     * @param null $viewStateGenerator
     *
     * @return mixed
     */
    public function getCPTCodes(int $page = 1, $viewState = null, $viewStateGenerator = null)
    {
        $uri = 'SharedFiles/popup/Popup.aspx?name=UserCPT';
        if($page === 1) {
            $response = $this->officeAlly->get($uri, [], true);
        } else {
            if(!$viewState || !$viewStateGenerator) {
                throw new \RuntimeException('__VIEWSTATE and __VIEWSTATEGENERATOR are required.');
            }
            $response = $this->officeAlly->post($uri, [
                'form_params' => [
                    '__LASTFOCUS' => null,
                    '__EVENTTARGET' => $page > 1 ? 'ctl04$popupBase$grvPopup' : '',
                    '__EVENTARGUMENT' => $page > 1 ? ('Page$' . $page) : '',
                    '__VIEWSTATE' => $viewState,
                    '__VIEWSTATEGENERATOR' => $viewStateGenerator,
                    'ctl04$popupBase$ddlSearch' => 'Code',
                    'ctl04$popupBase$ddlCondition' => '{0}%',
                    'ctl04$popupBase$txtSearch' => '',
                    'ctl04$popupBase$txtSearch2' => '',
                    'ctl04$popupBase$hdnSearch2ImgState' => 'closed',
                    'ctl04$popupBase$hdnShowSearch2' => 'none',
                ],
            ], true);
        }
    
        return $response->getBody()->getContents();
    }
    
    /**
     * @param int  $page
     * @param null $viewState
     * @param null $viewStateGenerator
     *
     * @return mixed
     */
    public function getEligibilityPayers(int $page = 1, $viewState = null, $viewStateGenerator = null)
    {
        $uri = 'SharedFiles/popup/Popup.aspx?name=BatchEligibilityPayer';
        if($page === 1) {
            $response = $this->officeAlly->get($uri, [], true);
            $crawler = new Crawler($response->getBody()->getContents());
            $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
            $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        }
        
        if(!$viewState || !$viewStateGenerator) {
            throw new \RuntimeException('__VIEWSTATE and __VIEWSTATEGENERATOR are required.');
        }
        
        $payload = [
            '__LASTFOCUS' => null,
            '__EVENTTARGET' => $page > 1 ? 'ctl04$popupBase$grvPopup' : '',
            '__EVENTARGUMENT' => $page > 1 ? ('Page$' . $page) : '',
            '__VIEWSTATE' => $viewState,
            '__VIEWSTATEGENERATOR' => $viewStateGenerator,
            'ctl04$popupBase$ddlSearch' => 'PayerName',
            'ctl04$popupBase$ddlCondition' => '{0}%',
            'ctl04$popupBase$txtSearch' => '',
            'ctl04$popupBase$txtSearch2' => '',
            'ctl04$popupBase$hdnSearch2ImgState' => 'closed',
            'ctl04$popupBase$hdnShowSearch2' => 'none',
        ];
        if($page === 1) {
            $payload['ctl04$popupBase$btnShowAll'] = 'Show All';
        }
        
        $response = $this->officeAlly->post($uri, [
            'form_params' => $payload,
        ], true);
        
        return $response->getBody()->getContents();
    }
    
    /**
     * @return int
     */
    public function getEligibilityPayersPageCount()
    {
        $uri = 'SharedFiles/popup/Popup.aspx?name=BatchEligibilityPayer';
        $response = $this->officeAlly->get($uri, [], true);
        $crawler = new Crawler($response->getBody()->getContents());
        $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
        $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        $payload = [
            '__LASTFOCUS' => null,
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            '__VIEWSTATE' => $viewState,
            '__VIEWSTATEGENERATOR' => $viewStateGenerator,
            'ctl04$popupBase$ddlSearch' => 'PayerName',
            'ctl04$popupBase$ddlCondition' => '{0}%',
            'ctl04$popupBase$txtSearch' => '',
            'ctl04$popupBase$btnShowAll' => 'Show All',
            'ctl04$popupBase$txtSearch2' => '',
            'ctl04$popupBase$hdnSearch2ImgState' => 'closed',
            'ctl04$popupBase$hdnShowSearch2' => 'none',
        ];
        
        $response = $this->officeAlly->post($uri, [
            'form_params' => $payload,
        ], true);
        $crawler = new Crawler($response->getBody()->getContents());
        $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
        $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');
        $payload['__EVENTTARGET'] = 'ctl04$popupBase$grvPopup';
        $payload['__EVENTARGUMENT'] = 'Page$Last';
        $payload['__VIEWSTATE'] = $viewState;
        $payload['__VIEWSTATEGENERATOR'] = $viewStateGenerator;
        unset($payload['ctl04$popupBase$btnShowAll']);
        
        $response = $this->officeAlly->post($uri, [
            'form_params' => $payload,
        ], true);
        $crawler = new Crawler($response->getBody()->getContents());
        $pageCount = $crawler->filterXPath("//tr[@class='GridviewPager']//table//tr[last()]/td[last()]");
        if($pageCount->count() > 0) {
            return (int)$pageCount->text();
        } else {
            return 0;
        }
    }
}