<?php

namespace App\Http\Requests\Patient\CreditCard;

use App\Http\Controllers\Utils\AccessUtils;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class Index extends FormRequest
{
    use AccessUtils;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check() && $this->isUserHasAccessRightsForPatient($this->route('patient')->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'force_sync' => 'boolean',
        ];
    }
}
