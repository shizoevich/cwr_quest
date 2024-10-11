<?php


namespace App\Http\Requests\PatientForm;


use App\Http\Controllers\Utils\AccessUtils;
use App\Models\Patient\PatientForm;
use Illuminate\Foundation\Http\FormRequest;

class ChangePatientFormStatus extends FormRequest
{
    use AccessUtils;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $patientForm = $this->route()->parameter('patient_form');
        return $patientForm->status === PatientForm::STATUS_NEW && $this->isUserHasAccessRightsForPatient($patientForm->patient_id);
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