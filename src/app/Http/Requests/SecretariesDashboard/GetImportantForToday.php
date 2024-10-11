<?php

namespace App\Http\Requests\SecretariesDashboard;

use Illuminate\Foundation\Http\FormRequest;

class GetImportantForToday extends FormRequest
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
            'start_date' => 'required|date_format:"Y-m-d"',
            'end_date' => 'nullable|date_format:"Y-m-d"',
        ];
    }
}
