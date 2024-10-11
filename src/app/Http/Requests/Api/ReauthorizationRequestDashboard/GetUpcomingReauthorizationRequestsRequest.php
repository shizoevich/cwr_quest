<?php

namespace App\Http\Requests\Api\ReauthorizationRequestDashboard;

use App\PatientInsurancePlan;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GetUpcomingReauthorizationRequestsRequest extends FormRequest
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
            'provider_id' => 'nullable|string',
            'search_text' => 'nullable|string',
            'patient_statuses' => 'nullable|array',
            'patient_statuses.*' => 'exists:patient_statuses,id',
            'expiration' => 'nullable|array',
            'expiration.*' => [Rule::in([
                PatientInsurancePlan::EXPIRATION_DOESNT_EXPIRE_ID,
                PatientInsurancePlan::EXPIRATION_EXPIRING_SOON_ID,
                PatientInsurancePlan::EXPIRATION_EXPIRED_ID
            ])],
        ];
    }
}
