<?php

namespace App\Http\Requests\Provider\Salary;

use Illuminate\Foundation\Http\FormRequest;

class AdditionalCompensation extends FormRequest
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
        ];
    }
}
