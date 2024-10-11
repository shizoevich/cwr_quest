<?php

namespace App\Http\Requests\Dashboard\Users;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendSmsToUpdateSignature extends FormRequest
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
        $rules = [
            'phone' => 'required|string',
            'user_id' => 'required|numeric|exists:users,id'
        ];

        return $rules;
    }
}
