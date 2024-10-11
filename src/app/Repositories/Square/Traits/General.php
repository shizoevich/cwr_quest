<?php

namespace App\Repositories\Square\Traits;

/**
 * Trait General
 * @package App\Repositories\Square\Traits
 */
trait General
{
    /**
     * @inheritDoc
     */
    public function getLocations()
    {
        $client = $this->getClient();
        $locations = $client->getLocationsApi()->listLocations();
        
        $locationsResult = null;
        if (isset($locations)) {
            $locationsResult = $locations->getResult();
        }

        if (isset($locationsResult)) {
            return $locationsResult->getLocations();
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCatalogObjects()
    {
        $client = $this->getClient();
        $catalogItems = $client->getCatalogApi()->listCatalog();
        
        $catalogItemsResult = null;
        if (isset($catalogItems)) {
            $catalogItemsResult = $catalogItems->getResult();
        }

        if (isset($catalogItemsResult)) {
            return $catalogItemsResult->getObjects();
        }

        return [];
    }
}