<?php

namespace App\Http\Requests\Api\ReauthorizationRequestDashboard;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStageRequest extends FormRequest
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
            'stage_id' => 'required|int|exists:submitted_reauthorization_request_form_stages,id',
            'comment' => 'nullable|string|max:1024'
        ];
    }
}
