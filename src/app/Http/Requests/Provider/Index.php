<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class Index extends FormRequest
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
            'page' => 'int|min:1',
            'limit' => 'int|min:1|max:100',
            'search_query' => 'nullable|string|max:255',
            'diagnoses' => 'nullable|array',
            'diagnoses.*' => 'required|int|exists:diagnoses,id',
        ];
    }
}
