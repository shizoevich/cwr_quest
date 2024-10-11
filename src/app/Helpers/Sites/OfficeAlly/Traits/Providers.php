<?php

namespace App\Helpers\Sites\OfficeAlly\Traits;

/**
 * Trait Providers
 * @package App\Helpers\Sites\OfficeAlly\Traits
 */
trait Providers
{
    /**
     * @return mixed|null
     */
    public function getProviderList()
    {
        $uri = 'ManageOffice/Default.aspx?Tab=O&Option=Provider_ViewList&jqGridID=ctl00_phFolderContent_ctlListProviders_myCustomGrid_myGrid&_search=false&rows=1000&page=1&sidx=&sord=asc';
    
        $response = $this->officeAlly->get($uri, [], true);
        $providers = json_decode($response->getBody()->getContents(), true);
        $providers = data_get($providers, 'rows');
        if($providers === null) {
            $this->officeAlly->notifyIfFailed('Providers list is not parsed.');
        }
    
        return $providers;
    }
    
    /**
     * @param int $providerId
     *
     * @return mixed
     */
    public function getProviderProfile($providerId)
    {
        $response = $this->officeAlly->get("ManageOffice/EditProvider.aspx?ID={$providerId}", [], true);
        
        return $response->getBody()->getContents();
    }
}