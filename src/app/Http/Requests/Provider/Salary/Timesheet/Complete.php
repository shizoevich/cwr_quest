<?php

namespace App\Http\Requests\Provider\Salary\Timesheet;

class Complete extends Base
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'billing_period_id'            => 'required|int|exists:billing_periods,id',
            'seek_time'                    => 'nullable|numeric|min:0',
            'monthly_meeting_attended'     => 'nullable|bool',
            'complaint'                    => 'nullable',
            'is_resolve_complaint'         => 'nullable|boolean',
        ];
    }
}
