<?php

namespace App\Http\Controllers\Api;

use App\Helpers\MandrillHelper;
use App\Http\Controllers\Controller;
use App\Models\MandrillRejectedEmail;
use App\Patient;
use App\Http\Requests\RemoveEmailFromRejectList;
use App\Events\NeedsWriteSystemComment;

class MandrillRejectedEmailController extends Controller
{
    public function removeEmailFromRejectList(RemoveEmailFromRejectList $request, Patient $patient)
    {
        if (!MandrillHelper::checkEmailInRejectList($request->input('email'))) {
            return response()->json(null, 404);
        }

        $rejectedEmail = MandrillRejectedEmail::where('email', $request->input('email'))->first();
        if (!$rejectedEmail) {
            return response()->json(null, 404);
        }
        if ($rejectedEmail->is_restored) {
            return response()->json(null, 400);
        }

        $isRemoved = MandrillHelper::removeEmailFromRejectList($request->input('email'));
        if (!$isRemoved) {
            return response()->json(null, 400);
        }

        $rejectedEmail->update([
            'is_restored' => true,
            'rejection_times' => 0,
        ]);

        $userMeta = \Auth::user()->meta;
        $comment = trans('comments.admin_removed_email_from_reject_list', [
            'email' => $request->input('email'),
            'user_name' => optional($userMeta)->getFullname() ?? '',
        ]);
        event(new NeedsWriteSystemComment($patient->id, $comment));

        return response()->json(null, 200);
    }
}
