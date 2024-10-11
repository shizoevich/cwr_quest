<?php


namespace App\Http\Requests\PatientForm;


use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SaveAllFormsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $documentRequest = $this->route()->parameter('document_request');
        return Carbon::createFromTimeString($documentRequest->expiring_at) > Carbon::now();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return  [
            'forms' => 'required|array',
            'forms.new_patient' => 'sometimes|array',
            'forms.confidential_information' => 'sometimes|array',
            'forms.telehealth' => 'sometimes|array',
            'forms.supporting_documents' => 'sometimes|array',
            'forms.supporting_documents.documents' => 'sometimes|array',
            'forms.supporting_documents.documents.*.type' => 'required|string',
            'forms.supporting_documents.documents.*.files' => 'required|array',
            'forms.supporting_documents.documents.*.files.*' => 'required|image|max:64000',
            'forms.payment_for_service' => 'sometimes|array',
            'signature_data' => 'required|array',
            'signature_data.signature' => 'required|string',
            'signature_data.signature18' => 'sometimes|string',
            'signature_data.relationship' => 'required_with:signature_data.signature18|string',
            'signature_data.guardian_name' => 'required_with:signature_data.signature18|string'
        ];
    }
    
    public function attributes()
    {
        return [
            'forms.supporting_documents.documents.*.files.*' => 'Supporting Documents',
        ];
    }
}