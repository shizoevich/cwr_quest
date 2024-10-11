<?php

namespace App\Repositories\NewPatientsCRM\PatientInquiryStage;

use Illuminate\Support\Collection;

interface PatientInquiryStageRepositoryInterface
{
    public function getAll(): Collection;
}