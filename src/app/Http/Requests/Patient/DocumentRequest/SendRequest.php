<?php

namespace App\Http\Requests\Patient\DocumentRequest;

use App\Http\Controllers\Utils\AccessUtils;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Patient\DocumentRequest\PatientFormType;

class SendRequest extends FormRequest
{
    use AccessUtils;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $patient = $this->route()->parameter('patient');

        return $this->isUserHasAccessRightsForPatient($patient->getKey());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $paymentForServiceId = PatientFormType::getPaymentForServiceId();

        return [
            'send_via_email'   => 'required|bool',
            'send_via_sms'     => 'required|bool',
            'email'            => 'nullable|email',
            'phone'            => 'nullable|string|max:14',
            'forms'            => "required|array|form_has_been_sent:{$paymentForServiceId}",
            'forms.*.name'     => 'required|string|max:100|exists:patient_form_types,name',
            'forms.*.metadata' => 'nullable|array',
            'forms.*.comment'  => 'nullable|string|max:1024',
        ];
    }
}
