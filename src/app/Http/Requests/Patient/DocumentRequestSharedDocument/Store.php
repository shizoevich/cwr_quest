<?php

namespace App\Http\Requests\Patient\DocumentRequestSharedDocument;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $patient = $this->route('encrypted_patient');
        $documentRequest = $this->route('document_request');
        
        return $patient->id == $documentRequest->patient_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:4|max:50',
        ];
    }
}
