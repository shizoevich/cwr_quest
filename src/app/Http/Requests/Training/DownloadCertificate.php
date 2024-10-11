<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class DownloadCertificate extends FormRequest
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
            'training_id' => 'required|integer|exists:trainings,id',
        ];
    }
}
