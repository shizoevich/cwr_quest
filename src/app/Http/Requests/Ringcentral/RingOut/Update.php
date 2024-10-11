<?php

namespace App\Http\Requests\Ringcentral\RingOut;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $callLog = $this->route('call_log');
        
        return auth()->id() == $callLog->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment' => 'nullable|string|max:1024',
        ];
    }
}
