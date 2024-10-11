<?php

namespace App\Http\Requests\Patient\ElectronicDocument;

use App\Http\Controllers\Utils\AccessUtils;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class Download extends FormRequest
{

    use AccessUtils;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $document = $this->route()->parameter('document');

        return $this->isUserHasAccessRightsForElectronicDocument($document->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

        ];
    }
}
