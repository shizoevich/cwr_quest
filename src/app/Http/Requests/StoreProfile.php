<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProfile extends FormRequest {

    protected $errorBag = 'info';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
        ];
    }
}
