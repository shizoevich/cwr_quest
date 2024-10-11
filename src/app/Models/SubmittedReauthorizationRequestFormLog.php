<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmittedReauthorizationRequestFormLog extends Model
{
    use SoftDeletes;

    const EMAIL_LOG_ID = 1;
    const FAX_LOG_ID = 2;
    const PHONE_LOG_ID = 3;

    protected $fillable = [
        'form_id',
        'log_type',
        'comment',
    ];
}
