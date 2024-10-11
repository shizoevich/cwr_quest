<?php


namespace App\Http\Requests\PatientForm;


use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCard extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $documentRequest = $this->route()->parameter('document_request');
        
        return Carbon::createFromTimeString($documentRequest->expiring_at) > Carbon::now();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return  [
            'nonce' => 'required|string|max:255',
            'zip' => 'nullable',
            'home_address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'email' => 'required|string|email',
            'mobile_phone' => 'nullable',
        ];
    }
}