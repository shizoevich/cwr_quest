<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsurancesPlanesPrices extends FormRequest
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
            'password' => 'required|string|max:255',
            'date_from' => 'nullable|date_format:"m/d/Y"',
            'prices' => 'array',
            'prices.*.insurance_id' => 'integer|exists:patient_insurances,id',
            'prices.*.plane_id' => 'integer|exists:patient_insurances_planes,id',
            'prices.*.procedure_id' => 'integer|exists:patient_insurances_procedures,id',
//            'prices.*.value' => 'float',
        ];
    }
}
