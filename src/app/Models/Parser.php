<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parser extends Model
{
    const SERVICE_OFFICEALLY = 'officeally';
    const SERVICE_TRIDIUUM= 'tridiuum';
    
    const STATUS_READY_TO_SYNCHRONIZATION= 0;
    
    const STATUS_SYNCHRONIZATION= 1;
    
    protected $fillable = [
        'service',
        'name',
        'title',
        'description',
        'started_at',
        'status',
        'allow_manual_start',
    ];
    
    protected $casts = [
        'status' => 'int',
        'allow_manual_start' => 'bool',
    ];
    
    protected $dates = ['started_at'];
    
    public $timestamps = false;
}
