<?php

namespace App\Helpers\Google;

class DirectoryService extends AbstractService
{

    /**
     * @return \Google_Service_Directory
     */
    public function getService(): \Google_Service
    {
        $client = $this->getClient();
        $client->addScope($this->getScopes());

        return new \Google_Service_Directory($client);
    }
}