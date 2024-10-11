<?php

namespace App\Http\Requests\PatientDocuments;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed document_id
 * @property mixed only_for_admin
 */
class ChangeDocumentStatus extends FormRequest
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
            'document_id' => 'required|numeric|exists:patient_documents,id',
            'only_for_admin' => 'required|boolean',
        ];
    }
}
