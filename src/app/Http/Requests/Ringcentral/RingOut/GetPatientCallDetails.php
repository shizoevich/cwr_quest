<?php

namespace App\Http\Requests\Ringcentral\RingOut;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientCallDetails extends FormRequest
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
            'patient_type' => 'nullable|string|max:64|in:patient,patient_lead',
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        return array_merge($data, $this->route()->parameters());
    }
}
