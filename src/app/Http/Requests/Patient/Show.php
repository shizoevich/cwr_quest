<?php

namespace App\Http\Requests\Patient;

use App\Http\Controllers\Utils\AccessUtils;
use Illuminate\Foundation\Http\FormRequest;

class Show extends FormRequest
{
    use AccessUtils;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $patient = $this->route('patient');
        
        return $this->isUserHasAccessRightsForPatient($patient->id, null, true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'with_tab_counts' => 'sometimes|accepted'
        ];
    }
}
