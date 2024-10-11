<?php

namespace App\Http\Requests\PatientLead\Api;

use Illuminate\Foundation\Http\FormRequest;

class AttachFaxRequest extends FormRequest
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
            'comment' => 'required|string',
            'fax_name' => 'nullable|string',
            'only_for_admin' => 'required|boolean',
            'status' => 'required|in:private,public',
        ];
    }
}
