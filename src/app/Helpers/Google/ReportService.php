<?php

namespace App\Helpers\Google;

class ReportService extends AbstractService
{

    /**
     * @return \Google_Service_Reports
     */
    public function getService(): \Google_Service
    {
        if(empty($this->getScopes())) {
            $this->setScopes([\Google_Service_Reports::ADMIN_REPORTS_AUDIT_READONLY]);
        }
        $client = $this->getClient();
        $client->addScope($this->getScopes());

        return new \Google_Service_Reports($client);
    }
}