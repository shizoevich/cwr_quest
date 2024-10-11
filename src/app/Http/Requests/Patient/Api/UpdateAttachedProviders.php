<?php

namespace App\Http\Requests\Patient\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttachedProviders extends FormRequest
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
            'providers' => 'required|array',
            'providers.*.id' => 'required|int|exists:providers,id',
            'providers.*.read_only' => 'required|bool',
        ];
    }
}
