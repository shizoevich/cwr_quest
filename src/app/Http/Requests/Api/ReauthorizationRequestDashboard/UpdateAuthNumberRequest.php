<?php

namespace App\Http\Requests\Api\ReauthorizationRequestDashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthNumberRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id|patient_insurance_expires',
            'auth_number' => 'required|string|max:50',
            'visits_auth' => 'required|numeric',
            'eff_start_date' => 'required|date',
            'eff_stop_date' => 'required|date|after_or_equal:eff_start_date',
        ];
    }
}
