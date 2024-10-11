<?php

namespace App\Http\Requests\SystemMessages;

use Illuminate\Foundation\Http\FormRequest;

class StoreSystemMessage extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'text' => 'required|string',
            'modal_class' => 'nullable|string|max:128',
//            'only_for_admin' => 'required|boolean',
        ];
    }
}
