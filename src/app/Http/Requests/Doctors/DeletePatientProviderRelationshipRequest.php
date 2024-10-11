<?php

namespace App\Http\Requests\Doctors;

use Illuminate\Foundation\Http\FormRequest;

class DeletePatientProviderRelationshipRequest extends FormRequest
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
            'providerId' => 'required|numeric|exists:providers,id',
            'patientId' => 'required|numeric|exists:patients,id',
            'reason' => 'nullable|string',
            'unassignAllProviders' => 'nullable|bool',
        ];
    }
}