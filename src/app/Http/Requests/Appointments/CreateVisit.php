<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class CreateVisit extends FormRequest
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
            'appointments' => 'required|array',
            'appointments.*.id' => 'required|int',
            'appointments.*.accept_change_cpt' => 'required|bool',
            'appointments.*.accept_change_pos' => 'required|bool',
            'appointments.*.accept_change_modifier_a' => 'required|bool',
            'appointments.*.supervisor_id' => 'nullable|exists:providers,id',
        ];
    }
}
