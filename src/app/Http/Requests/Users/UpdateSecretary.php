<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecretary extends FormRequest
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
            'lastname' => 'required|string',
            'firstname' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'lastname.required' => trans('validation.required', ['attribute' => 'Last name']),
            'lastname.string' => trans('validation.string', ['attribute' => 'Last name']),
            'firstaname.required' => trans('validation.required', ['attribute' => 'First name']),
            'firstname.string' => trans('validation.string', ['attribute' => 'First name'])
        ];
    }
}
