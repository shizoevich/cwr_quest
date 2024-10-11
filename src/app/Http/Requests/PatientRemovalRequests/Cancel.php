<?php

namespace App\Http\Requests\PatientRemovalRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed patient_id
 * @property mixed reason
 */
class Cancel extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check() && !\Auth::user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'reason'     => 'required|string|max:255',
        ];
    }
}
