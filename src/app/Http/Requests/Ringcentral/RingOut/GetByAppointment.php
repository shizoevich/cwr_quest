<?php

namespace App\Http\Requests\Ringcentral\RingOut;

use App\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class GetByAppointment extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var Appointment $appointment */
        $appointment = $this->route('appointment');
        
        return auth()->user()->isAdmin() || (auth()->user()->provider_id && auth()->user()->provider_id == $appointment->providers_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
