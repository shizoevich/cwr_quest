<?php

namespace App\Http\Requests\PatientInquiry\Api;

use App\Models\Patient\Lead\PatientLead;
use App\Patient;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'cell_phone' => 'required|string|max:20',
            'sex' => ['required', 'string', Rule::in(['F', 'M', 'U'])],
            'city' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'diagnoses.*.id' => 'nullable|integer',
            'diagnoses.*.code' => 'nullable|string|max:255',
            'eligibility_payer_id' => 'nullable|integer',
            'email' => 'nullable|email|max:255',
            'secondary_email' => 'nullable|email|max:255',
            'first_name' => 'required|string|max:255',
            'home_phone' => 'nullable|string|max:20',
            'insurance_id' => 'nullable|integer',
            'is_payment_forbidden' => 'boolean',
            'last_name' => 'required|string|max:255',
            'marketing_activity' => 'nullable|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'plan_name' => 'nullable|string|max:255',
            'preferred_language_id' => 'nullable|integer',
            'preferred_phone' => 'nullable|string|max:1',
            'provider_id' => 'nullable|integer',
            'registration_method_id' => 'required|integer',
            'source_id' => 'nullable|integer',
            'stage_id' => 'nullable|integer',
            'state' => 'nullable|string|max:255',
            'subscriber_id' => [
                'nullable',
                'string',
                'max:255',
                // Rule::unique('patients')->where(function (Builder $query) {
                //     return $query->when($this->inquirable_classname === class_basename(Patient::class), function (Builder $query) {
                //         $query->where('patients.id', '!=', $this->inquirable_id);
                //     });
                // }),
                // Rule::unique('patient_leads')->where(function (Builder $query) {
                //     return $query->when($this->inquirable_classname === class_basename(PatientLead::class), function (Builder $query) {
                //         $query->where('patient_leads.id', '!=', $this->inquirable_id);
                //     });
                // })
            ],
            'auth_number' => 'nullable|string|max:50',
            'visits_auth' => 'nullable|numeric',
            'visits_auth_left' => 'nullable|numeric',
            'therapy_type_id' => 'nullable|exists:patient_therapy_types,id',
            'eff_start_date' => 'nullable|date',
            'eff_stop_date' => 'nullable|date|after_or_equal:eff_start_date',
            'templates.*.pos' => 'nullable|string|max:255',
            'templates.*.cpt' => 'nullable|string|max:255',
            'templates.*.modifier_a' => 'nullable|string|max:255',
            'templates.*.modifier_b' => 'nullable|string|max:255',
            'templates.*.modifier_c' => 'nullable|string|max:255',
            'templates.*.modifier_d' => 'nullable|string|max:255',
            'templates.*.diagnose_pointer' => 'nullable|string|max:4',
            'templates.*.charge' => 'nullable|numeric|min:0',
            'templates.*.days_or_units' => 'nullable|integer|min:1',
            'therapist_manage_timesheet' => 'boolean',
            'is_self_pay' => 'nullable|boolean',
            'self_pay' => 'nullable|numeric|min:0',
            'visit_copay' => 'nullable|numeric|min:0',
            'deductible' => 'nullable|numeric|min:0',
            'deductible_met' => 'nullable|numeric|min:0|lte:deductible',
            'deductible_remaining' => 'nullable|numeric|min:0|lte:deductible',
            'insurance_pay' => 'nullable|numeric|min:0',
            'work_phone' => 'nullable|string|max:20',
            'zip' => 'nullable|string|max:10',

            'inquirable_id' => [
                'required_with:inquirable_classname',
                'exists_in_patients_or_patient_leads',
                'unique_firstname_lastname_date_of_birth',
            ],
            'inquirable_classname' => [
                'required_with:inquirable_id',
                Rule::in([class_basename(Patient::class), class_basename(PatientLead::class)])
            ],
        ];
    }
}
