<?php

namespace App\Http\Requests\PatientTransfer;

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
            'created_at_display_depth' => 'nullable|int',
            'patient_created_at_display_depth' => 'nullable|int',
        ];
    }
}
