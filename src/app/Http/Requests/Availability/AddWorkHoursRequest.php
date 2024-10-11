<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class AddWorkHoursRequest extends FormRequest
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
            'office_id' => 'required|exists:offices,id',
            'office_room_id' => ['nullable', 'exists:office_rooms,id'],
            'start_date' => ['required', 'date', 'provider_availability'],
            'length' => ['required', 'integer', 'min:60'],
            'in_person' => ['nullable', 'boolean'],
            'virtual' => ['nullable', 'boolean'],
            'availability_type_id' => ['required', 'exists:availability_types,id'],
            'availability_subtype_id' => ['nullable', 'exists:availability_subtypes,id'],
            'comment' => ['nullable', 'string']
        ];
    }
}
