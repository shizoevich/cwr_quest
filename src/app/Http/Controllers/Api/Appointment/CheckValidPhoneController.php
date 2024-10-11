<?php

namespace App\Http\Controllers\Api\Appointment;

use App\Exceptions\PhoneIsUnableToReceiveSmsException;
use App\Http\Requests\Twilio\CheckPhoneRequest;
use App\Services\Twilio\TwilioLookup;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class CheckValidPhoneController extends Controller
{
    public function check(CheckPhoneRequest $request): JsonResponse
    {
        try {
            TwilioLookup::validatePhone($request['phone']);
        } catch (PhoneIsUnableToReceiveSmsException $e) {
            return response()->json([
                "phone" => [
                    $e->getMessage(),
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
