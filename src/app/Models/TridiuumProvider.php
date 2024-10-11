<?php

namespace App\Models;

use App\Provider;
use Illuminate\Database\Eloquent\Model;

class TridiuumProvider extends Model
{
    protected $fillable = [
        'external_id',
        'internal_id',
        'name',
        'first_name',
        'last_name',
        'custom_reassigned_at',
        'parsed_at',
    ];
    
    protected $casts = [
        'internal_id' => 'int',
    ];
    
    protected $dates = [
        'custom_reassigned_at',
        'parsed_at',
    ];
    
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'internal_id');
    }
}
