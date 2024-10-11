<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MandrillRejectedEmail extends Model
{
    protected $fillable = [
        'email',
        'rejection_times',
        'is_restored'
    ];

    protected $casts = [
        'is_restored' => 'boolean',
    ];
}
