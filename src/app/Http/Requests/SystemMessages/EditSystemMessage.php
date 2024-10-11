<?php

namespace App\Http\Requests\SystemMessages;

use Illuminate\Foundation\Http\FormRequest;

class EditSystemMessage extends FormRequest
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
            'id' => 'required|numeric|exists:system_messages,id',
            'title' => 'nullable|string|max:255',
            'text' => 'required|string',
            'modal_class' => 'nullable|string|max:128'
        ];
    }
}
