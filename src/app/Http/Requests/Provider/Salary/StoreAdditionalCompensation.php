<?php

namespace App\Http\Requests\Provider\Salary;

use App\Models\Provider\Salary;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdditionalCompensation extends FormRequest
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
            'billing_period_id' => 'required|int|exists:billing_periods,id',
            'additional_compensation' => 'required|array',
            'additional_compensation.*.type' => 'nullable|int|in:' . implode(',', Salary::ADDITIONAL_COMPENSATION_TYPES),
            'additional_compensation.*.paid_fee' => 'required',
            'additional_compensation.*.additional_data' => 'required',
            'additional_compensation.*.notes' => 'nullable',
        ];
    }
}
