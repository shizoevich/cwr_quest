<?php

namespace Tests\Traits;

use App\Models\Officeally\OfficeallyTransactionType;

trait OfficeallyTransactionTypeTrait
{
    public function generateOfficeallyTransactionType(array $data = [])
    {
        return factory(OfficeallyTransactionType::class)->create($data);
    }
}