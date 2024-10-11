<?php

namespace App\Http\Requests\Appointments;

use App\Enums\VisitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class Update extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = Auth::user();

        return !$user->isAdmin()
            ? $user->isOnlyProvider()
                ? in_array($this->route('appointment')->id, $user->provider->appointments()->pluck('id')->all())
                : false
            : true;
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
            'time' => 'required|day_time',
            'office_id' => 'required|exists:offices,id',
            'visit_type' => 'required|visit_type',
            'reason_for_visit' => 'required|exists:treatment_modalities,id',
            'office_room' => 'required_if:visit_type,' . VisitType::IN_PERSON,
            'notes' => 'nullable|string|max:255',
            'send_telehealth_link_via_email' => 'sometimes|boolean',
            'send_telehealth_link_via_secondary_email' => 'sometimes|boolean',
            'send_telehealth_link_via_sms' => 'sometimes|boolean',
            'email' => 'sometimes|nullable|email',
            'secondary_email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string',
            'telehealth_provider' => 'sometimes|nullable|string',
            'allow_to_join_by_phone' => 'sometimes|nullable|boolean',
            'telehealth_notification_date' => 'sometimes|nullable|date'
        ];
    }

    /**
     * @return array
     */
    protected function providerRules(): array
    {
        return [
            'provider_id' => 'required|exists:providers,id|provider_appointment',
            'time' => 'required|day_time',
            'office_id' => 'required|exists:offices,id',
            'visit_type' => 'required|visit_type',
            'reason_for_visit' => 'required|exists:treatment_modalities,id',
            'office_room' => 'required_if:visit_type,' . VisitType::IN_PERSON,
            'notes' => 'nullable|string|max:255',
            'send_telehealth_link_via_email' => 'sometimes|boolean',
            'send_telehealth_link_via_secondary_email' => 'sometimes|boolean',
            'send_telehealth_link_via_sms' => 'sometimes|boolean',
            'email' => 'sometimes|nullable|email',
            'secondary_email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string',
            'telehealth_provider' => 'sometimes|nullable|string',
            'allow_to_join_by_phone' => 'sometimes|nullable|boolean',
            'telehealth_notification_date' => 'sometimes|nullable|date'
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