<?php

namespace App\Repositories\NewPatientsCRM\PatientLead;

use App\Models\Patient\Lead\PatientLead;

interface PatientLeadRepositoryInterface
{
    public function create(array $data): PatientLead;

    public function update(array $data, PatientLead $patientLead): PatientLead;

    public function getInquirablesWithoutActiveInquiries(int $limit, int $page, $searchQuery): array;
}