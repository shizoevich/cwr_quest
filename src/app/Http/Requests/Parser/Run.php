<?php

namespace App\Http\Requests\Parser;

use Illuminate\Foundation\Http\FormRequest;

class Run extends FormRequest
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
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|int|exists:parsers,id|distinct',
        ];
    }
}
