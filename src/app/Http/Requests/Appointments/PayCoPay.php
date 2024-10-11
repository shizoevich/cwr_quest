<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Officeally\OfficeallyTransactionType;
use Illuminate\Validation\Rule;

class PayCoPay extends FormRequest
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
            'appointment_id' => 'required|numeric|exists:appointments,id',
            'method' => [
                'nullable',
                'string',
                Rule::in([
                    OfficeallyTransactionType::CASH_PAYMENT_METHOD,
                    OfficeallyTransactionType::CHECK_PAYMENT_METHOD,
                    OfficeallyTransactionType::CREDIT_CARD_PAYMENT_METHOD
                ])
            ],
            'payment_amount' => 'required|numeric|min:0',
            'check_no' => 'nullable|string',
            'transaction_purpose_id' => 'required|exists:officeally_transaction_purposes,id'
        ];
    }
}
