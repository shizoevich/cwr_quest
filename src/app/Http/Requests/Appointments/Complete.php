<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class Complete extends FormRequest
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
            'appointmentId' => 'required|numeric|exists:appointments,id',
            'is_telehealth' => 'required|bool',
            'reason_for_visit' => 'required|numeric|exists:treatment_modalities,id',
            'visit_frequency_id' => 'required|numeric|exists:patient_visit_frequencies,id',
            'change_visit_frequency_comment' => 'nullable|string',
            'comment' => 'nullable|string',
        ];
    }
}
