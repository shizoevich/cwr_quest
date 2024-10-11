<?php

namespace App\Http\Requests\Api\PatientDocument;

use Illuminate\Foundation\Http\FormRequest;

class LoadTridiuumInitialAssessmentRequest extends FormRequest
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
            'appointment_id' => 'required|int|exists:appointments,id',
            'patient_id' => 'required|int|exists:patients,id',
        ];
    }
}
