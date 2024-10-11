<?php

namespace App\Http\Requests\Appointments\CompleteAppointment;

use App\Models\Square\SquarePaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Pay extends FormRequest
{
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $appointment = $this->route('appointment');
        $patient = $this->route('patient');
        if($appointment->patients_id != $patient->id) {
            abort(404);
        }
        
        return auth()->user()->isAdmin() || $appointment->patients_id == $patient->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $paymentMethods = SquarePaymentMethod::all();
        
        return [
            'amount' => 'required|integer|min:0',
            'payment_method_id' => [
                'required',
                'integer',
                Rule::in($paymentMethods->pluck('id')->toArray())
            ],
            'email' => [
                'required_if:payment_method_id,' . $paymentMethods->where('slug', 'invoice')->first()->id,
                'email',
            ],
            'check_no' => [
                'required_if:payment_method_id,' . $paymentMethods->where('slug', 'check')->first()->id,
                'string',
                'max:32',
            ]
        ];
    }
}
