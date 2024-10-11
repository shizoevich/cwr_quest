<?php

namespace App\Models\Provider;

use App\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryTimesheetLateCancellation extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'id',
        'appointment_id',
        'billing_period_id',
        'provider_id',
        'patient_id',
        'date',
        'amount',
        'is_custom_created',
        'accepted_at',
        'declined_at',
    ];
    
    protected $casts = [
        'id' => 'int',
        'appointment_id' => 'int',
        'billing_period_id' => 'int',
        'patient_id' => 'int',
        'provider_id' => 'int',
        'is_custom_created' => 'bool',
    ];
    
    protected $dates = [
        'accepted_at',
        'declined_at',
    ];
    
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    
    /**
     * @param $value
     *
     * @return float|int
     */
    public function getAmountAttribute($value)
    {
        return money_round($value / 100);
    }
    
    /**
     * @param $value
     *
     * @return void
     */
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = intval(floatval($value) * 100);
    }
}
