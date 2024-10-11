<?php

namespace App\Http\Requests\Patient\Comment;

use App\PatientComment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'patient_id' => 'required|numeric|exists:patients,id',
            'provider_id' => 'nullable|numeric|exists:providers,id',
            'appointment_id' => 'nullable|numeric|exists:appointments,id',
            'comment' => 'nullable|string',
            'comment_type' => Rule::in([
                PatientComment::DEFAULT_COMMENT_TYPE,
                PatientComment::CANCELLATION_COMMENT_TYPE,
                PatientComment::RESCHEDULE_COMMENT_TYPE,
                PatientComment::CREATION_COMMENT_TYPE,
                PatientComment::CHANGE_VISIT_FREQUENCY_TYPE,
                PatientComment::START_FILLING_REFERRAL_FORM_COMMENT_TYPE,
            ]),
            'metadata' => 'nullable|array',
            'metadata.old_time' => 'nullable|date_format:U',
            'metadata.new_time' => 'nullable|date_format:U',
        ];
    }
}
