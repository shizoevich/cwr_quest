<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserProfile extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'personal_email' => [
                'required',
                'email',
                'max:255',
            ],
            'school' => [
                'required',
                'string',
                'max:255',
            ],
            'complete_education' => [
                'required',
                'date',
            ],
            'years_of_practice' => [
                'required',
                'numeric',
                'min:0',
            ],
            'credentials' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^([\d]{3}-){2}[\d]{4}$/'
            ],
            'bio' => [
                'nullable',
                'string',
            ],
            'group_npi' => [
                'nullable',
                'digits:10',
            ],
            'tridiuum_external_url' => [
                'nullable',
                'url',
            ],
            'patient_categories' => [
                'nullable',
                'array',
            ],
            'patient_categories.*' => [
                'required_with:patient_categories',
                'int',
                'exists:therapist_survey_patient_categories,id',
            ],
            'ethnicities' => [
                'nullable',
                'array',
            ],
            'ethnicities.*' => [
                'required_with:ethnicities',
                'int',
                'exists:therapist_survey_ethnicities,id',
            ],
            'languages_tridiuum' => [
                'nullable',
                'array',
            ],
            'languages_tridiuum.*' => [
                'required_with:languages_tridiuum',
                'int',
                'exists:therapist_survey_languages,id',
            ],
            'races' => [
                'nullable',
                'array',
            ],
            'races.*' => [
                'required_with:races',
                'int',
                'exists:therapist_survey_races,id',
            ],
            'specialties' => [
                'nullable',
                'array',
            ],
            'specialties.*' => [
                'required_with:specialties',
                'int',
                'exists:therapist_survey_specialties,id',
            ],
            'treatment_types' => [
                'nullable',
                'array',
            ],
            'treatment_types.*' => [
                'required_with:treatment_types',
                'int',
                'exists:therapist_survey_treatment_types,id',
            ],
            'photo' => [
                'nullable',
                'image'
            ],
            'photo_name' => [
                'nullable',
                'string'
            ],
            'is_accept_video_appointments' => [
                'bool',
            ],
        ];
    }

    public function messages()
    {
        return [
            'years_of_practice.required' => 'A field is required',
            'phone.required' => 'A field is required',
            'years_of_practice.numeric'  => 'A field must be numeric',
            'phone.regex'  => 'Invalid phone format. Example: 111-111-1111',
        ];
    }
}
