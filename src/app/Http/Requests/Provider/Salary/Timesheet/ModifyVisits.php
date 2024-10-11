<?php

namespace App\Http\Requests\Provider\Salary\Timesheet;

class ModifyVisits extends Base
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'billing_period_id' => 'required|int|exists:billing_periods,id',
            'delete' => 'nullable|array',
            'delete.*' => 'required|int|exists:salary_timesheet_visits,id',
            'edit' => 'nullable|array',
            'edit.*.id' => 'required|int|exists:salary_timesheet_visits,id',
            'edit.*.is_overtime' => 'required|bool',
            'create.*.date' => 'required|date_format:"Y-m-d"',
            'create.*.is_overtime' => 'required|bool',
            'create.*.is_telehealth' => 'required|bool',
            'create.*.patient_id' => 'required|int|exists:patients,id',
            'create.*.billing_period_id' => 'required|int|exists:billing_periods,id',
        ];
    }
}
