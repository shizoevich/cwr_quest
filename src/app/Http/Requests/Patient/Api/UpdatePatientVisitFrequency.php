<?php

namespace App\Http\Requests\Patient\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientVisitFrequency extends FormRequest
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
            'patient_id' => 'required|numeric|exists:patients,id',
            'visit_frequency_id' => 'required|numeric|exists:patient_visit_frequencies,id',
            'comment' => 'nullable|string'
        ];
    }
}
