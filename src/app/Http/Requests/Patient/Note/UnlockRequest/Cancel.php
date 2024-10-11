<?php

namespace App\Http\Requests\Patient\Note\UnlockRequest;

use Illuminate\Foundation\Http\FormRequest;

class Cancel extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !auth()->user()->isAdmin();
    }

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
