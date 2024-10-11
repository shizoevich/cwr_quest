<?php

namespace App\Http\Requests\OfficeRoom;

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
            'from' => 'string|max:45|date_format:Y-m-d H:i',
            'to' => 'string|max:45|date_format:Y-m-d H:i',
        ];
    }
}
