<?php

namespace App\Http\Requests\PatientTransfer;

use Illuminate\Foundation\Http\FormRequest;

class TransferPatient extends FormRequest
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
            'patient_id' => 'required|int|exists:patients,id',
            'new_provider_id' => 'required|int|exists:providers,id',
            'old_provider_id' => 'required|int|exists:providers,id',
            'reason' => 'required|string',
        ];
    }
}
