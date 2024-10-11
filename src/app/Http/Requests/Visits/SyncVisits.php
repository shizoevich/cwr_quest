<?php

namespace App\Http\Requests\Visits;

use Illuminate\Foundation\Http\FormRequest;

class SyncVisits extends FormRequest
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
            'sync_by' => [
                'required',
                'string',
                'max:32',
                'regex:/^(date)|(month)|(therapist)|(visit)$/'
            ],
            'sync_date' => [
                'nullable',
                'date_format:m/d/Y'
            ],
            'sync_month' => [
                'nullable',
                'date_format:m/d/Y'
            ],
            'sync_provider' => [
                'nullable',
                'numeric',
                'exists:providers,officeally_id',
            ],
            'sync_visit' => [
                'nullable',
                'numeric',
                'exists:patient_visits,visit_id',
            ],
        ];
    }
}
