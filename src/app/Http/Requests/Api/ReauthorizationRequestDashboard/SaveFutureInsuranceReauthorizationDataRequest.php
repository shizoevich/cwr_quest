<?php

namespace App\Http\Requests\Api\ReauthorizationRequestDashboard;

use Illuminate\Foundation\Http\FormRequest;

class SaveFutureInsuranceReauthorizationDataRequest extends FormRequest
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
            'auth_number' => 'nullable|string|max:50',
            'visits_auth' => 'nullable|numeric',
            'eff_start_date' => 'nullable|date',
            'eff_stop_date' => 'nullable|date|after_or_equal:eff_start_date',
        ];
    }
}
