<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTridiuumCredentials extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userId = $this->get('user_id');
        $user = User::whereKey($userId)->firstOrFail();

        $required = 'required';
        if (optional($user->provider)->tridiuum_username) {
            $required = 'nullable';
        }

        return [
            'tridiuum_username' => [$required, 'string', 'min:2', 'max:255', "check_tridiuum_auth:{$this->request->get('tridiuum_username')},{$this->request->get('tridiuum_password')}", Rule::unique('providers')->where(function ($query) use ($user) {
                /** @var Builder $query */
                if (optional($user->provider)->tridiuum_username) {
                    return $query->where('tridiuum_username', '<>', optional($user->provider)->tridiuum_username);
                }
            })],
            'tridiuum_password' => ['nullable', 'required_with:tridiuum_username', 'string', 'min:2'],
        ];
    }

    public function messages()
    {
        return [
            "tridiuum_username.check_tridiuum_auth" => "Couldn’t find this account on Tridiuum. Please make sure to enter valid login details."
        ];
    }
}
