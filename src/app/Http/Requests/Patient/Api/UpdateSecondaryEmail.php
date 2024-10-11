<?php

namespace App\Http\Requests\Patient\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecondaryEmail extends FormRequest
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
            'secondary_email' => 'required|email',
        ];
    }
}
