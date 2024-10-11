<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class ChartDocuments extends FormRequest
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
            'page' => 'sometimes|required|int|min:1',
            'types' => 'sometimes|required|array',
            'types.*' => 'required|string|in:PatientComment,PatientPrivateComment,PatientAlert,InitialAssessment,PatientDocument,PatientPrivateDocument,PatientNote,CallLog,TelehealthSession',
        ];
    }
}
