<?php

namespace App\Http\Requests\PatientInquiry\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'id' => ['required', 'exists_in_patients_or_patient_leads'],
            'first_name' => 'sometimes|required|string|max:45',
            'last_name' => 'sometimes|required|string|max:45',
            'middle_initial' => 'nullable|string|max:45',
            'date_of_birth' => 'nullable|date_format:"Y-m-d"',
            'sex' => 'sometimes|required|string|sex',
            'preferred_language_id' => 'nullable|int|exists:languages,id',
            'email' => 'nullable|email',
            'secondary_email' => 'nullable|email',
            'cell_phone' => 'nullable|string|max:14',
            'home_phone' => 'nullable|string|max:14',
            'work_phone' => 'nullable|string|max:14',
            'preferred_phone' => 'nullable|patient_preferred_phone',
            'address' => 'nullable|string',
            'address_2' => 'nullable|string',
            'city' => 'nullable|string|max:45',
            'state' => 'nullable|string|state|max:2',
            'zip' => 'nullable|string|max:20',
            'provider_id' => 'nullable|exists:providers,id',
            'insurance_id' => 'nullable|exists:patient_insurances,id',
            'subscriber_id' => 'nullable|string|max:64|patient_subscriber',
            'plan_name' => 'nullable|string|max:255',
            'visit_copay' => 'nullable|numeric|min:0',
            'therapy_type_id' => 'nullable|exists:patient_therapy_types,id',
            'eligibility_payer_id' => 'nullable|exists:eligibility_payers,id',
            'auth_number' => 'nullable|string|max:50',
            'visits_auth' => 'nullable|numeric',
            'visits_auth_left' => 'nullable|numeric',
            'eff_start_date' => 'nullable|date',
            'eff_stop_date' => 'nullable|date|after_or_equal:eff_start_date',
            'diagnoses' => 'array',
            'diagnoses.*.id' => 'required|int|exists:diagnoses,id',
            'templates' => 'array',
            'templates.*.pos' => 'nullable|digits_between:1,10',
            'templates.*.patient_insurances_procedure_id' => 'nullable|exists:patient_insurances_procedures,id',
            'templates.*.modifier_a' => 'nullable|string|max:2|min:1',
            'templates.*.modifier_b' => 'nullable|string|max:2|min:1',
            'templates.*.modifier_c' => 'nullable|string|max:2|min:1',
            'templates.*.modifier_d' => 'nullable|string|max:2|min:1',
            'templates.*.diagnose_pointer' => 'nullable|string|max:4',
            'templates.*.charge' => 'nullable|numeric|min:0',
            'templates.*.days_or_units' => 'nullable|integer|min:1',
            'is_payment_forbidden' => 'nullable|boolean',
            'model_classname' => ['required', Rule::in(['Patient', 'PatientLead'])]
        ];
    }
}
