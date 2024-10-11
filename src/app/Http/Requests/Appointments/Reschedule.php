<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\VisitType;
use App\Models\AppointmentRescheduleSubStatus;
use Illuminate\Validation\Rule;

class Reschedule extends FormRequest
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
            'id' => ['required', 'exists:appointments,id'],
            'patient_id' => ['required', 'exists:patients,id', 'patient_appointment'],
            'provider_id' => ['required', 'exists:providers,id', 'provider_appointment'],
            'date' => ['required', 'date_start_today', 'max_appointments_per_day:12', 'appointment_rescheduling_according_to_patient_visit_frequency'],
            'time' => ['required', 'day_time'],
            'office_id' => ['required', 'exists:offices,id'],
            'office_room' => ['required_if:visit_type,' . VisitType::IN_PERSON],
            'visit_type' => ['required', 'visit_type'],
            'comment' => ['required', 'string', 'max:1024'],
            'reason_for_visit' => 'required|exists:treatment_modalities,id',
            'send_telehealth_link_via_email' => 'sometimes|boolean',
            'send_telehealth_link_via_secondary_email' => 'sometimes|boolean',
            'send_telehealth_link_via_sms' => 'sometimes|boolean',
            'email' => 'sometimes|nullable|email',
            'secondary_email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string',
            'telehealth_provider' => 'sometimes|nullable|string',
            'allow_to_join_by_phone' => 'sometimes|nullable|boolean',
            'telehealth_notification_date' => 'sometimes|nullable|date',
            'reschedule_sub_status_id' => Rule::in(AppointmentRescheduleSubStatus::getAllIds()),
        ];
    }
}
