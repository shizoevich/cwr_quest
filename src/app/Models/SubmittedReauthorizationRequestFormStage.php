<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class SubmittedReauthorizationRequestFormStage extends Model
{
    public const STAGE_READY_TO_SEND = 'Ready to send';
    public const STAGE_SENT = 'Sent';
    public const STAGE_APPROVAL_RECEIVED = 'Approval received';
    public const STAGE_REJECTED = 'Rejected';
    public const STAGE_EDIT_REQUIRED = 'Edit required';
    public const STAGE_AUTH_UPDATED = 'Auth. updated';
    public const STAGE_ARCHIVED = 'Archived';
    public const STAGE_OTHER = 'Other';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public static function getReadyToSendId(): int
    {
        return Cache::rememberForever('submitted_reauthorization_request_form_stage-ready_to_send', function () {
            return self::where('name', self::STAGE_READY_TO_SEND)->first()->id;
        });
    }

    public static function getApprovalReceivedId(): int
    {
        return Cache::rememberForever('submitted_reauthorization_request_form_stage-approval_received', function () {
            return self::where('name', self::STAGE_APPROVAL_RECEIVED)->first()->id;
        });
    }
}
