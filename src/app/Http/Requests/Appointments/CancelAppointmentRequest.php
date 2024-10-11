<?php

namespace App\Http\Requests\Appointments;

use App\Patient;
use App\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelAppointmentRequest extends FormRequest
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
        $patient = Patient::find($this->input('patient_id'));
        $chargeForCancellationAppointment = $patient->getLateCancellationAmount();

        return [
            'appointmentId' => 'required|exists:appointments,id',
            'status' => ['required', Rule::in(Status::getOtherCancelStatusesId())],
            'charge_for_cancellation' => "required|numeric|min:0|max:$chargeForCancellationAppointment",
            'patient_requested_cancellation_at' => 'nullable|date|patient_requested_cancellation_at',
            'comment' => 'required|string|max:1024',
        ];
    }
}
