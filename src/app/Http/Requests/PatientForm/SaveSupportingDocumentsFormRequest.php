<?php


namespace App\Http\Requests\PatientForm;


class SaveSupportingDocumentsFormRequest extends BaseSavePatientFormRequest
{
    public function rules()
    {
        $rules = [
            'documents' => 'required|array',
            'documents.*.type' => 'required|string',
            'documents.*.files' => 'required|array',
            'documents.*.files.*' => 'required|image|max:64000'
        ];
        return array_merge(parent::rules(), $rules);
    }
}