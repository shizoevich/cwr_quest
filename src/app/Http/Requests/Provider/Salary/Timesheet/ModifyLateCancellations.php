<?php

namespace App\Http\Requests\Provider\Salary\Timesheet;

class ModifyLateCancellations extends Base
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'delete' => 'nullable|array',
            'delete.*' => 'required|int|exists:salary_timesheet_visits,id',
            'create.*.date' => 'required|date_format:"Y-m-d"',
            'create.*.amount' => 'required|numeric',
            'create.*.patient_id' => 'required|int|exists:patients,id',
            'create.*.billing_period_id' => 'required|int|exists:billing_periods,id',
        ];
    }
}
