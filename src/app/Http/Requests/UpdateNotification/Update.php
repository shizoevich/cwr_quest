<?php

namespace App\Http\Requests\UpdateNotification;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
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
            'show_date' => 'nullable|date',
            'is_required' => 'nullable|boolean',
            'title' => 'required|string|min:2|max:255',
            'content' => 'required|string|max:16777215',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'user_ids.required' => 'The users field is required.',
        ];
    }
}
