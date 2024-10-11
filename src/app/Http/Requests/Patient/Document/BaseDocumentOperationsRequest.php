<?php

namespace App\Http\Requests\Patient\Document;


use App\Http\Controllers\Utils\AccessUtils;
use App\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseDocumentOperationsRequest extends FormRequest
{
    use AccessUtils;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var Patient $patient */
        $patient = $this->route()->parameter('patient');
        return $this->isUserHasAccessRightsForPatient($patient->getKey());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'documents' => 'required|array',
            'documents.*' => [
                'required',
                Rule::exists('patient_documents', 'id')
                    ->where('patient_id', $this->route()->parameter('patient')->id)
            ],
        ];
    }
}
