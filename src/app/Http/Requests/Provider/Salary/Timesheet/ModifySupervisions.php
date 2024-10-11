<?php

namespace App\Http\Requests\Provider\Salary\Timesheet;

class ModifySupervisions extends Base
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'billing_period_id' => 'required|exists:billing_periods,id',
            'supervisions' => 'nullable|array',
            'supervisions.*.provider_id' => 'required|exists:providers,id',
            'supervisions.*.supervisor_id' => 'required|exists:providers,id',
            'supervisions.*.supervision_hours' => 'required|numeric|between:0,6',
            'supervisions.*.comment' => 'required|string',
        ];
    }
}
