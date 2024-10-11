<?php

namespace App\Http\Requests\PatientForms;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhoto extends FormRequest
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
            'patient_photo' => 'image',
        ];
    }
}
