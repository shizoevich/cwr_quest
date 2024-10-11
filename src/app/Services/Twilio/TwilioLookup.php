<?php

namespace App\Services\Twilio;

use App\Exceptions\PhoneIsUnableToReceiveSmsException;
use App\Models\PhoneNumber;
use Twilio\Rest\Lookups;
use Twilio\Rest\Client;

class TwilioLookup
{
    /**
     * @return bool
     * @throws PhoneIsUnableToReceiveSmsException
     */
    public static function validatePhone($phone)
    {
        if (config('app.env') !== 'production') {
            return true;
        }

        $validatedPhone = PhoneNumber::query()->where('sanitized_phone', sanitize_phone($phone))->first();
        if ($validatedPhone) {
            if ($validatedPhone->isSmsAllowed()) {
                return true;
            }
            
            throw new PhoneIsUnableToReceiveSmsException($phone);
        }
        
        $client = new Lookups(new Client(
            config('twilio.twilio.connections.twilio.sid'),
            config('twilio.twilio.connections.twilio.token')
        ));
        $lookup = $client->phoneNumbers
            ->getContext($phone)
            ->fetch(['CountryCode' => 'US', 'Type' => 'carrier']);
        
        if (isset($lookup->carrier) && isset($lookup->carrier->type)) {
            $validatedPhone = PhoneNumber::query()->updateOrCreate([
                'sanitized_phone' => sanitize_phone($phone),
            ], [
                'phone'           => $lookup->phone_number,
                'country_code'    => $lookup->country_code,
                'carrier_type'    => $lookup->carrier->type,
                'carrier_name'    => $lookup->carrier->name,
            ]);
            if ($validatedPhone->isSmsAllowed()) {
                return true;
            }
            
            throw new PhoneIsUnableToReceiveSmsException($phone);
        }
        
        return false;
    }
}