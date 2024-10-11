<?php

namespace App\Http\Requests\Api\ReauthorizationRequestDashboard;

use Illuminate\Foundation\Http\FormRequest;

class GetSubmittedReauthorizationRequestFormsRequest extends FormRequest
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
            'submitted_by' => 'nullable|string',
            'search_text' => 'nullable|string',
            'patient_statuses' => 'nullable|array',
            'patient_statuses.*' => 'exists:patient_statuses,id',
            'stages' => 'nullable|array',
            'stages.*' => 'exists:submitted_reauthorization_request_form_stages,id',
        ];
    }
}
