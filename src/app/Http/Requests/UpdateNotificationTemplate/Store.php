<?php

namespace App\Http\Requests\UpdateNotificationTemplate;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
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
            'name' => 'required|string|min:2|max:255|unique:update_notification_templates,name',
            'notification_title' => 'required|string|min:2|max:255',
            'notification_content' => 'required|string|max:16777215',
        ];
    }
}
