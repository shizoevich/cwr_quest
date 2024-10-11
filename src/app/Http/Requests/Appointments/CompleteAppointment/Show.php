<?php

namespace App\Http\Requests\Appointments\CompleteAppointment;

use Illuminate\Foundation\Http\FormRequest;

class Show extends FormRequest
{
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $appointment = $this->route('appointment');
        $patient = $this->route('patient');
        if($appointment->patients_id != $patient->id) {
            abort(404);
        }
        
        return auth()->user()->isAdmin() || $appointment->patients_id == $patient->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        
        ];
    }
}
