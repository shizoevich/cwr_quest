<?php

namespace App\Observers;

use App\Models\Patient\Lead\PatientLead;

class PatientLeadObserver
{
    public function creating(PatientLead $patientLead)
    {
        $this->sanitizePhones($patientLead);
    }

    public function updating(PatientLead $patientLead)
    {
        $this->sanitizePhones($patientLead);
    }

    private function sanitizePhones(PatientLead $patientLead)
    {
        if ($patientLead->home_phone) {
            $patientLead->home_phone = sanitize_phone($patientLead->home_phone);
        }

        if ($patientLead->cell_phone) {
            $patientLead->cell_phone = sanitize_phone($patientLead->cell_phone);
        }

        if ($patientLead->work_phone) {
            $patientLead->work_phone = sanitize_phone($patientLead->work_phone);
        }
    }
}
