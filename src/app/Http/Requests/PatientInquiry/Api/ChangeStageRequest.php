<?php

namespace App\Http\Requests\PatientInquiry\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStageRequest extends FormRequest
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
            'stage_id' => 'required|int|exists:patient_inquiry_stages,id|change_stage_requirements_are_met',
            'comment' => 'nullable|string',
            'forms' => 'nullable|array',
            'forms.*.name' => 'required|string|max:100|exists:patient_form_types,name',
            'forms.*.metadata' => 'nullable|array',
            'onboarding_data' => 'nullable|array',
            'onboarding_data.date' => 'nullable|date',
            'onboarding_data.time' => 'nullable|string',
            'onboarding_data.phone' => 'nullable|string',
            'send_via_email' => 'nullable|boolean',
            'email' => 'nullable|email|required_if:send_via_email,true',
            'send_via_sms' => 'nullable|boolean',
            'phone' => 'nullable|string|required_if:send_via_sms,true',
        ];
    }
}
