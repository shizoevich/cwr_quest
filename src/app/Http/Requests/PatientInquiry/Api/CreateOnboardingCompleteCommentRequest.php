<?php

namespace App\Http\Requests\PatientInquiry\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateOnboardingCompleteCommentRequest extends FormRequest
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
            'phone' => 'required|string|max:14'
        ];
    }
}
