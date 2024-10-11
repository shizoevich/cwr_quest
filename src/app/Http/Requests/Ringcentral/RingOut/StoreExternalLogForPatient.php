<?php

namespace App\Http\Requests\Ringcentral\RingOut;

use Illuminate\Foundation\Http\FormRequest;

class StoreExternalLogForPatient extends FormRequest
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
            'phone_from' => 'required',
            'phone_to' => 'required',
            'patient_id' => 'required|int',
            'patient_type' => 'required|string|max:64|in:patient,patient_lead',
            'only_for_admin' => 'nullable|boolean'
        ];
    }
}
