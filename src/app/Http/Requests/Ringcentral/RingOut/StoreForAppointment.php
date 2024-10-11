<?php

namespace App\Http\Requests\Ringcentral\RingOut;

use Illuminate\Foundation\Http\FormRequest;

class StoreForAppointment extends FormRequest
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
            'play_prompt' => 'boolean',
            'appointment_id' => 'required|int',
            'appointment_type' => 'required|string|max:64|in:appointment,tridiuum_appointment',
        ];
    }
}
