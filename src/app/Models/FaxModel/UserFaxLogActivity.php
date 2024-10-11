<?php

namespace App\Models\FaxModel;

use Illuminate\Database\Eloquent\Model;

class UserFaxLogActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'url',
        'method',
        'agent',
        'user_id',
        'fax_id',
        'patient_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'subject'=> 'string',
        'url'=> 'string',
        'method'=> 'string',
        'agent'=> 'string',
        'user_id' => 'integer',
        'fax_id' => 'integer',
        'patient_id' => 'integer',
    ];  
}
