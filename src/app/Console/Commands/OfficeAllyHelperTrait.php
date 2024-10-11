<?php

namespace App\Console\Commands;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;

trait OfficeAllyHelperTrait
{
    private $officeAllyHelpers = [];

    private function initOfficeAllyHelpers()
    {
        $accounts = Option::getOptionValue('officeally_credentials');
        $accounts = json_decode($accounts, true);

        foreach (array_keys($accounts) as $accountName) {
            $this->officeAllyHelpers[$accountName] = app()->make(OfficeAllyHelper::class)($accountName);
        }
    }

    private function officeAllyHelper(): OfficeAllyHelper
    {
        return $this->officeAllyHelpers[array_rand($this->officeAllyHelpers)];
    }
}