<?php

namespace App\Http\Requests\Patient;

use App\Http\Controllers\Utils\AccessUtils;
use App\Models\Patient\PatientAdditionalPhone;
use App\PatientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends FormRequest
{
    use AccessUtils;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->isUserHasAccessRightsForPatient($this->route('patient')->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status_id' => 'nullable|integer|min:1|exists:patient_statuses,id',
            'email' => 'nullable|email|max:255',
            'cell_phone' => 'nullable|string',
            'home_phone' => 'nullable|string',
            'work_phone' => 'nullable|string',
            'cell_phone_label' => 'nullable|string',
            'home_phone_label' => 'nullable|string',
            'work_phone_label' => 'nullable|string',
            'additional_phones' => 'nullable|array',
            'additional_phones.*.id' => 'nullable|integer|min:1|exists:patient_additional_phones',
            'additional_phones.*.phone' => 'required|string',
            'additional_phones.*.label' => 'required|string|max:255',
            'additional_phones_phone_type' => [
                'required_with:additional_phones',
                Rule::in(PatientAdditionalPhone::PHONE_TYPES),
            ],
            'comment' => 'nullable|string',
        ];
    }

    /**
     * @return array
     */
    public function credentials(): array
    {
        $credentials = [];
        if($this->has('status_id')) {
            $credentials['status_id'] = $this->get('status_id');
        }
        if($this->exists('email')) {
            $credentials['email'] = $this->get('email');
        }
        if($this->exists('cell_phone')) {
            $credentials['cell_phone'] = $this->get('cell_phone');
            $credentials['parse_cell_phone'] = false;
        }
        if($this->exists('home_phone')) {
            $credentials['home_phone'] = $this->get('home_phone');
            $credentials['parse_home_phone'] = false;
        }
        if($this->exists('work_phone')) {
            $credentials['work_phone'] = $this->get('work_phone');
            $credentials['parse_work_phone'] = false;
        }
        if($this->exists('cell_phone_label')) {
            $credentials['cell_phone_label'] = $this->get('cell_phone_label');
        }
        if($this->exists('home_phone_label')) {
            $credentials['home_phone_label'] = $this->get('home_phone_label');
        }
        if($this->exists('work_phone_label')) {
            $credentials['work_phone_label'] = $this->get('work_phone_label');
        }
        
        return $credentials;
    }

    /**
     * @return bool
     */
    public function hasArchiveAction(): bool
    {
        return $this->get('status_id') == PatientStatus::getArchivedId();
    }

    /**
     * @return bool
     */
    public function hasAdditionalPhones(): bool
    {
        return $this->exists('additional_phones');
    }
}
