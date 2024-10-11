<?php

namespace App\Repositories\NewPatientsCRM\PatientLead;

use App\Enums\PatientPreferredPhone;
use App\Models\Patient\Lead\PatientLead;
use App\Patient;
use App\PatientInsurancePlan;
use App\PatientStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PatientLeadRepository implements PatientLeadRepositoryInterface
{
    public function create(array $data): PatientLead
    {
        $patientLead = PatientLead::create($this->preparePatientLeadData($data));

        if (!empty($data['diagnoses'])) {
            $diagnoseIds = collect($data['diagnoses'])->pluck('id');
            $patientLead->diagnoses()->sync($diagnoseIds);
        }

        if (!empty($data['templates'])) {
            $patientLead->templates()
                ->each(function ($template) {
                    $template->delete();
                });
            foreach ($data['templates'] as $key => $template) {
                if ($this->checkTemplateFields($template)) {
                    $template['position'] = $key;
                    $patientLead->templates()->create($template);
                }
            }
        }

        return $patientLead;
    }

    public function update(array $data, PatientLead $patientLead): PatientLead
    {
        $patientLead->update($this->preparePatientLeadData($data));

        if (!empty($data['diagnoses'])) {
            $diagnoseIds = collect($data['diagnoses'])->pluck('id');
            $patientLead->diagnoses()->sync($diagnoseIds);
        }

        if (!empty($data['templates'])) {
            $patientLead->templates()
                ->each(function ($template) {
                    $template->delete();
                });
            foreach ($data['templates'] as $key => $template) {
                if ($this->checkTemplateFields($template)) {
                    $template['position'] = $key;
                    $patientLead->templates()->create($template);
                }
            }
        }

        return $patientLead;
    }

    public function getInquirablesWithoutActiveInquiries(int $limit, int $page, $searchQuery): array
    {
        $patientsQuery = Patient::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'middle_initial',
                DB::raw('IF(middle_initial != "", CONCAT(first_name, " ", middle_initial, " ", last_name), CONCAT(first_name, " ", last_name)) as full_name'),
                'cell_phone',
                'home_phone',
                'work_phone',
                'preferred_phone',
                'address',
                'address_2',
                'city',
                'state',
                'zip',
                'primary_provider_id',
                'primary_insurance_id',
                'insurance_plan_id',
                'subscriber_id',
                'visit_copay',
                'therapy_type_id',
                'eligibility_payer_id',
                DB::raw('0 as therapist_manage_timesheet'),
                'preferred_language_id',
                'sex',
                'email',
                'secondary_email',
                'date_of_birth',
                'status_id',
                'auth_number',
                'visits_auth',
                'visits_auth_left',
                'eff_start_date',
                'eff_stop_date',
                'is_payment_forbidden',
                'is_self_pay',
                'self_pay',
                'deductible',
                'deductible_met',
                'deductible_remaining',
                'insurance_pay',
                DB::raw('1 as is_patient'),
                DB::raw('0 as is_lead'),
                DB::raw("'" . class_basename(Patient::class) . "' as inquirable_classname")
            ])
            ->with(['templates', 'insurance', 'status', 'inquiries', 'diagnoses', 'eligibilityPayer', 'insurancePlan'])
            ->whereDoesntHave('inquiries', function ($query) {
                $query->active();
            })
            ->orderBy('full_name')
            // @todo: temporary fix because stage server throwing 500 error
            ->limit(500);

        if (!empty($searchQuery)) {
            $patientsQuery->havingRaw('full_name like ?', [$searchQuery . '%']);
        }

        $patients = $patientsQuery->get();

        $patientsLeadsQuery = PatientLead::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'middle_initial',
                DB::raw('IF(middle_initial != "", CONCAT(first_name, " ", middle_initial, " ", last_name), CONCAT(first_name, " ", last_name)) as full_name'),
                'cell_phone',
                'home_phone',
                'work_phone',
                'preferred_phone',
                'address',
                'address_2',
                'city',
                'state',
                'zip',
                'provider_id',
                'primary_insurance_id',
                'insurance_plan_id',
                'subscriber_id',
                'visit_copay',
                'therapy_type_id',
                'eligibility_payer_id',
                DB::raw('NULL as therapist_manage_timesheet'),
                'preferred_language_id',
                'sex',
                'email',
                'secondary_email',
                'date_of_birth',
                'auth_number',
                'visits_auth',
                'visits_auth_left',
                'eff_start_date',
                'eff_stop_date',
                'is_payment_forbidden',
                DB::raw('0 as is_patient'),
                DB::raw('1 as is_lead'),
                DB::raw('NULL as status_id'),
                DB::raw("'" . class_basename(PatientLead::class) . "' as inquirable_classname")
            ])
            ->with('insurance', 'insurancePlan', 'templates', 'inquiries', 'diagnoses')
            ->whereNull('patient_id')
            ->whereDoesntHave('inquiries', function ($query) {
                $query->active();
            })
            ->orderBy('full_name');

        if (!empty($searchQuery)) {
            $patientsLeadsQuery->havingRaw('full_name like ?', [$searchQuery . '%']);
        }

        $patientsLeads = $patientsLeadsQuery->get();

        $combinedResults = $patients->merge($patientsLeads)->sortBy('full_name')->toArray();

        $results = new LengthAwarePaginator(
            array_slice($combinedResults, ($page - 1) * $limit, $limit),
            count($combinedResults),
            $limit,
            $page
        );

        return $results->toArray();
    }

    protected function preparePatientLeadData(array $data): array
    {
        $planNameId = !empty($data['plan_name']) && !empty($data['insurance_id'])
            ? PatientInsurancePlan::firstOrCreate([
                'insurance_id' => $data['insurance_id'],
                'name' => $data['plan_name'],
            ])->id : null;

        return [
            'first_name' => $data['first_name'],
            'middle_initial' => $data['middle_initial'] ?? '',
            'last_name' => $data['last_name'],
            'sex' => $data['sex'] ?? null,
            'provider_id' => $data['provider_id'] ?? null,
            'preferred_language_id' => $data['preferred_language_id'] ?? null,
            'email' => $data['email'] ?? null,
            'secondary_email' => $data['secondary_email'] ?? null,
            'address' => $data['address'] ?? null,
            'address_2' => $data['address_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip' => $data['zip'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'cell_phone' => $data['cell_phone'] ?? null,
            'home_phone' => $data['home_phone'] ?? null,
            'work_phone' => $data['work_phone'] ?? null,
            'preferred_phone' => array_search($data['preferred_phone'] ?? null, PatientPreferredPhone::$list) ?: null,
            'primary_insurance_id' => $data['insurance_id'] ?? null,
            'subscriber_id' => $data['subscriber_id'] ?? null,
            'insurance_plan_id' => $planNameId,
            'is_self_pay' => $data['is_self_pay'] ?? null,
            'self_pay' => $data['self_pay'] ?? null,
            'visit_copay' => $data['visit_copay'] ?? null,
            'deductible' => $data['deductible'] ?? null,
            'deductible_met' => $data['deductible_met'] ?? null,
            'deductible_remaining' => $data['deductible_remaining'] ?? null,
            'insurance_pay' => $data['insurance_pay'] ?? null,
            'therapy_type_id' => $data['therapy_type_id'] ?? null,
            'auth_number' => $data['auth_number'] ?? null,
            'visits_auth' => $data['visits_auth'] ?? null,
            'visits_auth_left' => $data['visits_auth_left'] ?? null,
            'eff_start_date' => $data['eff_start_date'] ?? null,
            'eff_stop_date' => $data['eff_stop_date'] ?? null,
            'eligibility_payer_id' => $data['eligibility_payer_id'] ?? null,
            'is_payment_forbidden' => $data['is_payment_forbidden'] ?? false
        ];
    }

    protected function checkTemplateFields(array $template): bool
    {
        return !empty($template['pos']) || !empty($template['patient_insurances_procedure_id']) || !empty($template['modifier_a']) || !empty($template['modifier_b'])
            || !empty($template['modifier_c']) || !empty($template['modifier_d']) || !empty($template['charge']) || !empty($template['days_or_units']);
    }
}
