<?php

namespace App\Http\Requests\PatientInquiry\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateInitialSurveyCommentRequest extends FormRequest
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
            'metadata.service_rate' => 'required|numeric|between:1,5',
            'metadata.provider_rate' => 'required|numeric|between:1,5',
            'metadata.suggestions' => 'nullable|string',
        ];
    }
}
