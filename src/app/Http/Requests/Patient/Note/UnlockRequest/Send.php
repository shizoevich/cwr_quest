<?php

namespace App\Http\Requests\Patient\Note\UnlockRequest;

use Illuminate\Foundation\Http\FormRequest;

class Send extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && !auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'patient_note_id' => [
                'required',
                'integer',
                'exists:patient_notes,id',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
