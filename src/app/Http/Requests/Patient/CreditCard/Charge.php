<?php

namespace App\Http\Requests\Patient\CreditCard;

use App\Http\Controllers\Utils\AccessUtils;
use Illuminate\Foundation\Http\FormRequest;

class Charge extends FormRequest
{
    use AccessUtils;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check() && $this->isUserHasAccessRightsForPatient($this->route('patient')->id);
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'required|integer|min:1',
            'card_nonce' => 'required_without:card_id|string',
            'card_id' => 'required_without:card_nonce|integer|exists:patient_square_account_cards,id|card_belongs_to_patient',
            'catalog_item_id' => 'required|integer|exists:square_catalog_items,id',
            'appointment_id' => 'required|integer|exists:appointments,id',
        ];
    }
}
