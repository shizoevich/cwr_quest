<?php

namespace App\Http\Requests\Patient\DocumentRequestSharedDocument;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class Show extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $shared = $this->route('shared');
        if($shared->isExpired()) {
            abort(410, 'Expired');
        }
        
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
            'password' => 'sometimes|required|string|max:50',
        ];
    }
}
