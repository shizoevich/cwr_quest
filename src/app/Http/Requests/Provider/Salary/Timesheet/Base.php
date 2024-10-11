<?php

namespace App\Http\Requests\Provider\Salary\Timesheet;

use Illuminate\Foundation\Http\FormRequest;

abstract class Base extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !!optional(auth()->user())->provider_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
