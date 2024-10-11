<?php

namespace App\Repositories\NewPatientsCRM\PatientInquirySource;

use App\Models\Patient\Inquiry\PatientInquirySource;
use Illuminate\Support\Collection;

interface PatientInquirySourceRepositoryInterface
{
    public function getAll(): Collection;

    public function createSource(array $data): PatientInquirySource;
}