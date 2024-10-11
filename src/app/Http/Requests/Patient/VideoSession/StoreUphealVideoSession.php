<?php

namespace App\Http\Requests\Patient\VideoSession;

use App\Http\Controllers\Utils\AccessUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUphealVideoSession extends FormRequest
{
    use AccessUtils;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $patient = $this->route('patient');
        
        return $this->isUserHasAccessRightsForPatient($patient->id) && !auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'send_via_email' => 'required|bool',
            'send_via_secondary_email' => 'required|bool',
            'send_via_sms'    => 'required|bool',
            'email'           => 'nullable|email',
            'secondary_email' => 'nullable|email',
            'phone'           => 'nullable|string|max:14',
            'appointment'     => 'required',
            'appointment.id' => [
                'required',
                'integer',
                Rule::exists('appointments', 'id')->whereNull('deleted_at')->where('patients_id', $this->route('patient')->id)
            ],
            'appointment.date' => 'nullable|date|after_or_equal:today',
            'provider_id' => 'required|exists:providers,id',
        ];
    }
}
