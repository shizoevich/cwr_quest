<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class GetWorkHoursRequest extends FormRequest
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
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'with_active_appointments' => 'nullable|boolean',
            'with_canceled_appointments' => 'nullable|boolean',
            'with_rescheduled_appointments' => 'nullable|boolean'
        ];
    }
}
