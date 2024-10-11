<?php

namespace App\Http\Requests\Patient\Api;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'assign' => 'nullable|boolean',
            'only_for_admin' => 'nullable|boolean',
            'fax_id' => 'nullable|exists:faxes,id',
            'fax_name' => 'nullable|string',
            'document_type_id' => 'nullable|exists:patient_document_types,id',
            'content' => 'nullable|string',
            'qqfile' => 'nullable|file|max:64000',
            'qqfilename' => 'string|required_with:qqfile',
        ];
    }
}
