<?php

namespace App\Http\Requests\Dashboard\Doctors;

use Illuminate\Foundation\Http\FormRequest;

class SaveProviderBillingPeriodRelation extends FormRequest
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
            'billingPeriodTypeId' => 'required|numeric|exists:billing_period_types,id',
            'providerId' => 'required|numeric|exists:providers,id',
        ];
    }
}
