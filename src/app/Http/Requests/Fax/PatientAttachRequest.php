<?php

namespace App\Http\Requests\Fax;

use Illuminate\Foundation\Http\FormRequest;

class PatientAttachRequest extends FormRequest
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
            'comment' => 'required|string|max:10000',
            'status' => 'string|max:100',
            'patient_id' => 'required|int|min:1',
            'fax_id' => 'required|int|min:1',
        ];
    }
}
