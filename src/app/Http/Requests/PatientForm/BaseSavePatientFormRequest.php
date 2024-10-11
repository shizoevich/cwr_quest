<?php


namespace App\Http\Requests\PatientForm;

use App\Http\Controllers\Utils\AccessUtils;
use App\Option;
use Illuminate\Foundation\Http\FormRequest;


class BaseSavePatientFormRequest extends FormRequest
{
    use AccessUtils;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Hash::check($this->input('password'), Option::getOptionValue('doctor_password'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}