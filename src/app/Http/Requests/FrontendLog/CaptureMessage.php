<?php

namespace App\Http\Requests\FrontendLog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed patient_id
 * @property mixed reason
 */
class CaptureMessage extends FormRequest
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
            'message' => 'required|string',
        ];
    }
}
