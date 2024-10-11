<?php

namespace App\Http\Requests\Dashboard\DocumentToSend;

use Illuminate\Foundation\Http\FormRequest;

class DischargeSummary extends FormRequest
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
            'page' => 'nullable|integer|min:1',
            'sent' => 'nullable|boolean',
            'to_send' => 'nullable|boolean',
            'approved' => 'nullable|boolean',
            'date' => 'nullable|date_format:"Y-m-d"',
            'provider_id' => 'nullable|integer',
        ];
    }
}
