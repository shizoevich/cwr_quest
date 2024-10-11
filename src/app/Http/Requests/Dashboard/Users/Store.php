<?php

namespace App\Http\Requests\Dashboard\Users;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Store extends FormRequest
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
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => [
                'required',
                'string',
                'email',
                'regex:/@(' . config('app.email-domain') . ')$/',
                'max:191',
                'unique:users',
            ],
            'personal_email' => [
                'required',
                'email',
                'regex:/@((?!' . config('app.email-domain') . '))((?!' . config('app.email-domain2') . ').)*$/',
            ],
            'user_role' => 'string|in:provider,secretary,patient_relation_manager'
        ];
        
        if ($this->request->get('user_role') === 'provider') {
            $rules['provider_id'] = 'required|integer|exists:providers,id|bail|allow_select_provider';
            $rules['tariff_plan_id'] = 'nullable|integer|exists:tariffs_plans,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'personal_email.regex'              => "Email domains " . config('app.email-domain') . " and " . config('app.email-domain2') . " are not allowed.",
            'provider_id.allow_select_provider' => 'You cannot assign this provider account.',
            'email.regex'                       => trans('validation.regex') . " Example: example@" . config('app.email-domain'),
        ];
    }
}
