<?php

namespace App\Http\Requests\Availability\Admin;

use App\Http\Requests\ApiRequest;
use App\Provider;
use Illuminate\Validation\Rule;

class Show extends ApiRequest
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
            'provider_id'               => 'nullable|int|exists:providers,id',
            'office_id'                 => 'nullable|int|exists:offices,id',
            'start'                     => 'nullable|date',
            'end'                       => 'nullable|date|after:start',
            'visit_types'               => 'nullable|array',
            'kaiser_types'              => 'nullable|array',
            'kaiser_types.*'             => [
                'required',
                Rule::in(Provider::KAISER_TYPES),
            ],
            'visit_types.*'             => 'required|int|in:1,2',
            'types_of_clients_id_all'   => 'nullable|array',
            'types_of_clients_id_all.*' => 'required|int|exists:therapist_survey_type_of_clients,id',
            'age_group_id_all'          => 'nullable|array',
            'age_group_id_all.*'        => 'required|int|exists:therapist_survey_age_groups,id',
            'languages'                 => 'nullable|array',
            'languages.*'               => 'required|string',
            'practice_focus_id_all'     => 'nullable|array',
            'practice_focus_id_all.*'   => 'required|int|exists:therapist_survey_practice_focus,id',
        ];
    }
}
