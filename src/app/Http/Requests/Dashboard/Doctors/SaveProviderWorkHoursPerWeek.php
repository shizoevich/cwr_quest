<?php

namespace App\Http\Requests\Dashboard\Doctors;

use Illuminate\Foundation\Http\FormRequest;

class SaveProviderWorkHoursPerWeek extends FormRequest
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
            'workHoursPerWeek' => 'required|integer|min:0',
            'providerId' => 'required|numeric|exists:providers,id',
        ];
    }
}
