<?php

namespace App\Http\Requests\PatientSquareAccount;

use Illuminate\Foundation\Http\FormRequest;

class Detach extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
        ];
    }
}
