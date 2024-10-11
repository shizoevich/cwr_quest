<?php

namespace App\Http\Requests\PatientRemovalRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @property mixed patient_id
 * @property mixed reason
 * @property mixed request_id
 */
class Decline extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'request_id' => 'required|integer|exists:patient_removal_requests,id',
            'reason' => 'required|string|max:255',
        ];
    }
}
