<?php

namespace App\Http\Requests\Patient\Note\UnlockRequest;

use Illuminate\Foundation\Http\FormRequest;

class Decline extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'request_id' => [
                'required',
                'integer',
                'exists:patient_note_unlock_requests,id'
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
