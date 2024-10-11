<?php

namespace Tests\Traits;

use App\Patient;

trait PatientTrait
{
    protected static function generatePatient($data = []): Patient
    {
        return factory(Patient::class)->create($data);
    }
}