<?php

namespace App\Http\Requests\Dashboard\Doctors;

use Illuminate\Foundation\Http\FormRequest;

class SaveProviderTariffPlanRelation extends FormRequest
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
            'tariffPlanId' => 'required|numeric|exists:tariffs_plans,id',
            'providerId' => 'required|numeric|exists:providers,id',
            'date_from' => 'nullable|date_format:"m/d/Y"',
        ];
    }
}
