<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CopyPreviousWeekRequest extends FormRequest
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
            'start' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $startDate = Carbon::parse($value)->endOfDay();
                    $nextMonday = Carbon::today()->next(Carbon::MONDAY)->startOfDay();
                    if ($startDate->lt($nextMonday)) {
                        $fail('The ' . $attribute . ' must be a date not earlier than ' . $nextMonday->format('m/d/Y'));
                    }
                },
            ],
        ];
    }
}
