<?php

use App\Enums\TimeEnum;

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'client-email'                => 'This isn\'t a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field must have a value.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'client-required'             => 'This field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute format is invalid.',

    'square' => [
        'payment_method_error' => [
            'address_verification_failure'           => 'Zip code is invalid.',
            'cardholder_insufficient_permissions'    => 'The card issuer has declined the transaction due to restrictions on where the card can be used.',
            'insufficient_funds'                     => 'The funding source has insufficient funds to cover the payment.',
            'card_expired'                           => 'The card issuer declined the request because the card is expired.',
            'card_not_supported'                     => 'The card is not supported either in the geographic region or by the MCC.',
            'chip_insertion_required'                => 'The card issuer requires that the card be read using a chip reader.',
            'cvv_failure'                            => 'The card issuer declined the request because the CVV code is invalid.',
            'expiration_failure'                     => 'The card expiration date is either invalid or indicates that the card is expired.',
            'invalid_account'                        => 'The card issuer was not able to locate account on record.',
            'invalid_fees'                           => 'The app fee money on a payment is too high.',
            'invalid_location'                       => 'The Square account cannot take payments in the specified region.',
            'invalid_postal_code'                    => 'The zip code is incorrectly formatted.',
            'manually_entered_payment_not_supported' => 'The card must be swiped, tapped, or dipped. Payments attempted by manually entering the card number are declined.',
            'pan_failure'                            => 'The specified card number is invalid.',
            'transaction_limit'                      => 'The card issuer has determined the payment amount is either too high or too low.',
            'voice_failure'                          => 'The card issuer declined the request because the issuer requires voice authorization from the cardholder.',
            'unsupported_entry_method'               => 'The entry method for the credit card (swipe, dip, tap) is not supported.',
            'invalid_expiration'                     => 'The expiration date for the payment card is invalid.',
            'generic_decline'                        => 'An unexpected error occurred.',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'old_password' => [
            'current_password_match' => 'Old password is incorrect.',
        ],
        'date' => [
            'date_start_today' => 'The selected date must be after or equal today.',
            'max_appointments_per_day' => 'Impossible to create more than :max appointments per day. Please change date.',
        ],
        'time' => [
            'day_time' => 'Time must be between ' . TimeEnum::START_DAY_RANGE . ' and ' . TimeEnum::END_DAY_RANGE . '.',
        ],
        'visit_length' => [
            'visit_length' => 'Visit length is invalid.'
        ],
        'visit_type' => [
            'visit_type' => 'Visit type is invalid.'
        ],
        'office_room' => [
            'office_room' => 'Office Room is invalid.'
        ],
        'patient_id' => [
            'patient_appointment' => 'Patient already has an appointment for the selected date.',
        ],
        'provider_id' => [
            'provider_appointment' => 'Therapist already has an appointment for the selected time.'
        ],
        'id' => [
            'exists_in_patients_or_patient_leads' => 'The selected id does not exist'
        ],
        'repeat' => [
            'appointment_repeat' => 'Appointment cannot be repeated due to the overlapping with other appointments.'
        ],
        'start_date' => [
            'provider_availability' => 'The selected time slot is already in use.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
