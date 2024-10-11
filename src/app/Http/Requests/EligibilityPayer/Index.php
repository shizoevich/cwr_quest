<?php

namespace App\Http\Requests\EligibilityPayer;

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
            'limit' => 'int|min:1|max:99',
            'search_query' => 'nullable|max:255',
        ];
    }
}
