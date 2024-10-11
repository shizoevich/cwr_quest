<?php

namespace App\Http\Requests\Dashboard\Insurance\Plan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'need_collect_copay_for_telehealth' => 'required|bool',
            'is_verification_required' => 'required|bool',
            'requires_reauthorization_document' => 'nullable|bool',
            'reauthorization_notification_visits_count' => 'nullable|integer|min:1',
            'reauthorization_notification_days_count' => 'nullable|integer|min:1'
        ];
    }
}
