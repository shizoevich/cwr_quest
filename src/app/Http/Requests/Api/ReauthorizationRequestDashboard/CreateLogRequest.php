<?php

namespace App\Http\Requests\Api\ReauthorizationRequestDashboard;

use App\Models\SubmittedReauthorizationRequestFormLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLogRequest extends FormRequest
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
            'log_type' => Rule::in([
                SubmittedReauthorizationRequestFormLog::EMAIL_LOG_ID,
                SubmittedReauthorizationRequestFormLog::FAX_LOG_ID,
                SubmittedReauthorizationRequestFormLog::PHONE_LOG_ID
            ]),
            'comment' => 'required|string|max:1024'
        ];
    }
}
