<?php

namespace App\Http\Requests\PatientInquiry\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetInquiriesByStageRequest extends FormRequest
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
            'stage_id' => 'required|exists:patient_inquiry_stages,id',
            'page' => 'required|integer|min:1',
            'search_text' => 'nullable|string',
            'source_id' => 'nullable|array|exists:patient_inquiry_sources,id',
        ];
    }
}
