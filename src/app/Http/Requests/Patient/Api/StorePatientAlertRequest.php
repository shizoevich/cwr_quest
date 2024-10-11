<?php

namespace App\Http\Requests\Patient\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientAlertRequest extends FormRequest
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
            'message' => 'required|string|max:128',
            'co_pay' => 'required|numeric|min:0',
            'deductible' => 'required|numeric|min:0',
            'deductible_met' => 'required|numeric|min:0|lte:deductible',
            'deductible_remaining' => 'required|numeric|min:0|lte:deductible',
            'insurance_pay' => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:128',
            'file' => 'nullable|file|max:64000',
        ];
    }
}
