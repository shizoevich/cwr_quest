<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FrontendLog\CaptureMessage as CaptureMessageRequest;
use App\Traits\LogDataTrait;

class FrontendLogController extends Controller
{
    use LogDataTrait;

    public function captureMessage(CaptureMessageRequest $request) {
        $this->logData('logs/frontend.log', $request->message);

        return response()->json([
            'success' => true
        ]);
    }
}
