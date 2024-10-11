<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetReadProviderMessage extends FormRequest
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
            'mention_id' => 'required|int|exists:patient_comment_mentions,id',
        ];
    }
}
