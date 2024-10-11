<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogDump extends Model
{
    use SoftDeletes;

    protected $guarded = [];
}
