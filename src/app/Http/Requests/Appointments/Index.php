<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class Index extends FormRequest
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
            'date' => 'string|max:45|date',
            'providers_id' => 'exists:providers,id',
            'offices_id' => 'array',
            'offices_id.*' => 'exists:offices,id',
            'send_telehealth_link_via_email' => 'boolean',
            'send_telehealth_link_via_sms' => 'boolean',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:12',
            'appointment_statuses' => 'nullable|array',
            'appointment_statuses.*' => 'required|int|exists:appointment_statuses,id'
        ];
    }
}
