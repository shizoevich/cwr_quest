<?php

namespace App\Http\Requests\Patient\Api;

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
            'document_id' => 'required|exists:patient_documents,id',
            'document_type_id' => 'required|exists:patient_document_types,id',
            'visible_only_for_admin' => 'required|boolean',
            'other_document_type' => 'nullable|string|max:255'
        ];
    }
}
