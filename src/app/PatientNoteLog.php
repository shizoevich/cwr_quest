<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientNoteLog extends Model
{
    const TYPE_CREATE_DRAFT = 1;
    const TYPE_UPDATE_DRAFT = 2;
    const TYPE_DELETE_DRAFT = 3;
    const TYPE_CREATE = 4;
    const TYPE_UPDATE = 5;
    const TYPE_DELETE = 6;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'patient_note_id',
        'patient_id',
        'provider_id',
        'user_id',
        'type',
        'data',
    ];
}
