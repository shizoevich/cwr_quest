<?php

namespace App\Http\Requests\Api\DoctorsRequestsDashboard;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientNoteUnlockRequestsListRequest extends FormRequest
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
            'request_statuses' => 'nullable|array',
            'request_statuses.*' => 'required|int',
            'patient_name' => 'nullable|string',
            'therapist_name' => 'nullable|string',
        ];
    }
}
