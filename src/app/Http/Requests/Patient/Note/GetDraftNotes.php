<?php


namespace App\Http\Requests\Patient\Note;


use App\Http\Controllers\Utils\AccessUtils;
use App\Patient;
use Illuminate\Foundation\Http\FormRequest;

class GetDraftNotes extends FormRequest
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
        return [];
    }
}