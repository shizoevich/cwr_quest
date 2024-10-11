<?php

namespace App\Http\Controllers\Api\Webhook\Twilio;

use App\Services\Twilio\TwilioSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SmsController extends Controller
{
    
    /** @var TwilioSmsService */
    private $twilioSmsService;

    public function __construct(TwilioSmsService $twilioSmsService)
    {
        $this->twilioSmsService = $twilioSmsService;
    }

    public function sms(Request $request): JsonResponse
    {
        $this->twilioSmsService->processSms($request->all());

        return response()->json();
    }
}
