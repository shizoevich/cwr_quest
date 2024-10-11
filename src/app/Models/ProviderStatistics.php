<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderStatistics extends Model
{
    public $table = 'providers_statistics';

    protected $fillable = [
        'provider_id',
        'billing_period_id',
        'initial_availability_length',
        'remaining_availability_length',
        'appointments_count',
        'appointments_length',
        'kaiser_appointments_count',
        'active_appointments_count',
        'active_appointments_length',
        'completed_appointments_count',
        'completed_appointments_length',
        'visit_created_appointments_count',
        'visit_created_appointments_length',
        'cancelled_appointments_count',
        'cancelled_appointments_length',
        'rescheduled_appointments_count',
        'rescheduled_appointments_length',
        'cancelled_by_patient_appointments_count',
        'cancelled_by_provider_appointments_count',
        'last_minute_cancel_by_patient_appointments_count',
        'patient_did_not_come_appointments_count',
        'cancelled_by_office_appointments_count',
        'cancelled_appointments_rate',
        'total_cancelled_appointments_rate',
        'avg_initial_availability_length',
        'avg_remaining_availability_length',
        'avg_appointments_count',
        'avg_appointments_length',
        'avg_active_appointments_count',
        'avg_active_appointments_length',
        'avg_completed_appointments_count',
        'avg_completed_appointments_length',
        'avg_visit_created_appointments_count',
        'avg_visit_created_appointments_length',
        'avg_cancelled_appointments_count',
        'avg_cancelled_appointments_length',
        'avg_cancelled_appointments_rate',
        'total_avg_cancelled_appointments_rate',
        'patients_count',
        'patients_with_visits_count',
        'new_patients_count',
        'transferred_patients_count',
        'total_revenue',
        'provider_revenue',
        'visits_count',
        'applied_visits_count'
    ];

    protected $casts = [
        
    ];

    public function getTotalRevenueAttribute($value)
    {
        return money_round($value / 100);
    }

    public function setTotalRevenueAttribute($value)
    {
        $this->attributes['total_revenue'] = intval(floatval($value) * 100);
    }
    
    public function getProviderRevenueAttribute($value)
    {
        return money_round($value / 100);
    }

    public function setProviderRevenueAttribute($value)
    {
        $this->attributes['provider_revenue'] = intval(floatval($value) * 100);
    }
}
