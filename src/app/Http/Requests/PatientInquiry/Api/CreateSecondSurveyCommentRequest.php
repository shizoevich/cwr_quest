<?php

namespace App\Http\Requests\PatientInquiry\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateSecondSurveyCommentRequest extends FormRequest
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
            'comment' => 'required|string|max:1024',
            'appointment_id' => 'required|exists:appointments,id',
            'metadata' => 'required|array',
            'metadata.therapist_understanding_support_rate' => 'required|numeric|between:1,5',
            'metadata.therapy_atmosphere_rate' => 'required|numeric|between:1,5',
            'metadata.therapist_openness_share_rate' => 'required|numeric|between:1,5',
            'metadata.therapy_session_after_feelings_rate' => 'required|numeric|between:1,5',
            'metadata.suggestions' => 'nullable|string',
        ];
    }
}
