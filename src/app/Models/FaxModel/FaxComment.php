<?php

namespace App\Models\FaxModel;

use Illuminate\Database\Eloquent\Model;

/**
 * FaxComment
 * @package App\Models\Fax;
 * 
 * @property int id
 * @property string description
 * @property-read \App\Patient $patient
 */
class FaxComment extends Model
{
    protected $table = 'fax_comments';

    /**
     * @var array
     */
    protected $guarded = [];
    
    /**
     * patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
