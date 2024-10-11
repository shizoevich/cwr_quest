<?php

namespace App\Http\Requests\Provider\Salary\Timesheet\Admin;

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
            'monthly_meeting_attended' => 'required|bool',
            'sick_times' => 'nullable|array',
            'sick_times.*.date' => 'required|date',
            'sick_times.*.appointments' => 'required|array',
            'sick_times.*.appointments.*' => 'required|int|exists:appointments,id',
            'supervisions' => 'nullable|array',
            'supervisions.*.provider_id' => 'required|exists:providers,id',
            'supervisions.*.supervisor_id' => 'required|exists:providers,id',
            'supervisions.*.supervision_hours' => 'required|numeric|between:0,4',
        ];
    }
}
