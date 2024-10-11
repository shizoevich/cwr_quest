<?php

namespace App\Http\Requests\Appointments;

use App\Enums\VisitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class Store extends FormRequest
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
        return Auth::user()->isAdmin() ? $this->adminRules() : $this->providerRules();
    }

    /**
     * @return array
     */
    protected function adminRules(): array
    {
        return [
            'provider_id' => 'required|exists:providers,id|provider_appointment',
            'patient_id' => 'required|exists:patients,id|patient_appointment',
            'date' => 'required|date_start_today|max_appointments_per_day:12',
            'time' => 'required|day_time',
            'office_id' => 'nullable|exists:offices,id',
            'visit_type' => 'required|visit_type',
            'reason_for_visit' => 'required|exists:treatment_modalities,id',
            'office_room' => 'required_if:visit_type,' . VisitType::IN_PERSON,
            'notes' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return array
     */
    protected function providerRules(): array
    {
        return [
            'provider_id' => 'required|exists:providers,id|provider_appointment',
            'patient_id' => 'required|exists:patients,id|patient_appointment',
            'date' => 'required|date_start_today|max_appointments_per_day:12',
            'time' => 'required|day_time',
            'office_id' => 'nullable|exists:offices,id',
            'visit_type' => 'required|visit_type',
            'reason_for_visit' => 'required|exists:treatment_modalities,id',
            'office_room' => 'required_if:visit_type,' . VisitType::IN_PERSON,
            'notes' => 'nullable|string|max:255',
            'repeat' => 'nullable|int|min:0|max:20|appointment_repeat',
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $user = Auth::user();

        if ($user->isOnlyProvider()) {
            $data['provider_id'] = $user->provider_id;
        }

        return $data;
    }
}
