<?php


namespace App\Http\Requests\Patient\Document;


use App\Http\Controllers\Utils\AccessUtils;
use App\Patient;
use App\PatientDocument;
use Illuminate\Foundation\Http\FormRequest;

class GetDocumentBase64 extends FormRequest
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
        /** @var PatientDocument $document */
        $document = $this->route()->parameter('patient_document');
        if ($patient->id !== $document->patient_id) {
            abort(404);
        }
        return $this->isUserHasAccessRightsForPatient($patient->getKey());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}