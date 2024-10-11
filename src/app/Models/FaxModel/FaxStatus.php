<?php

namespace App\Models\FaxModel;

use Illuminate\Database\Eloquent\Model;

/**
 * FaxStatus
 * @package App\Models\Fax;
 * 
 * @property int id
 * @property string status
 * @property-read \App\Patient $patient
 */
class FaxStatus extends Model
{
    protected $table = 'fax_statuses';

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
