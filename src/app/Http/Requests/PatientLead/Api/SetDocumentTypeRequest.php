<?php

namespace App\Http\Requests\PatientLead\Api;

use Illuminate\Foundation\Http\FormRequest;

class SetDocumentTypeRequest extends FormRequest
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
            'document_type_id' => 'required|numeric|exists:patient_document_types,id',
            'visible_only_for_admin' => 'required|boolean',
        ];
    }
}
