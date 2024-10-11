<?php

namespace App\Http\Requests\PatientTransactions;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionAdjustment extends FormRequest
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
            'amount' => 'required|integer',
            'comment' => 'required|string',
            'patient_id' => 'required|integer|exists:patients,id',
        ];
    }
}
