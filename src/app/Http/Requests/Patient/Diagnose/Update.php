<?php

namespace App\Http\Requests\Patient\Diagnose;

use App\Http\Controllers\Utils\AccessUtils;
use Illuminate\Foundation\Http\FormRequest;

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
        return \Auth::check() && $this->isUserHasAccessRightsForPatient($this->route('patient')->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'diagnoses' => 'required|array',
            'diagnoses.*' => 'required|int|exists:diagnoses,id',
        ];
    }
}
