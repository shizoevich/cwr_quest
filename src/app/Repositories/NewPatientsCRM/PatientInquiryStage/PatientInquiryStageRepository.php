<?php

namespace App\Repositories\NewPatientsCRM\PatientInquiryStage;

use App\Models\Patient\Inquiry\PatientInquiryStage;
use Illuminate\Support\Collection;

class PatientInquiryStageRepository implements PatientInquiryStageRepositoryInterface
{
    public function getAll(): Collection
    {
        return PatientInquiryStage::all();
    }
}