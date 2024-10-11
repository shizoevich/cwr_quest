<?php

namespace App\Models\Patient\Lead;

use App\Models\Diagnose;
use App\Models\FaxModel\Fax;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Models\PatientTherapyType;
use App\PatientInsurance;
use App\PatientInsurancePlan;
use App\Models\RingcentralCallLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Scopes\PatientDocuments\DocumentsForAllScope;

class PatientLead extends Model
{
    use SoftDeletes;

    protected $table = 'patient_leads';

    protected $guarded = [];

    protected $casts = [
        'is_payment_forbidden' => 'boolean'
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo('patients', 'patient_id', 'id');
    }

    public function diagnoses(): BelongsToMany
    {
        return $this->belongsToMany(Diagnose::class, 'patient_lead_diagnoses');
    }

    public function insurance()
    {
        return $this->belongsTo(PatientInsurance::class, 'primary_insurance_id', 'id');
    }

    public function insurancePlan()
    {
        return $this->belongsTo(PatientInsurancePlan::class, 'insurance_plan_id', 'id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(PatientLeadTemplate::class, 'patient_lead_id', 'id');
    }

    public function inquiries(): MorphMany
    {
        return $this->morphMany(PatientInquiry::class, 'inquirable');
    }

    public function lastFiveRingcentralCallLogs(): MorphMany
    {
        return $this->morphMany(RingcentralCallLog::class, 'call_subject')
            ->orderByDesc('id')
            ->take(5);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PatientLeadComment::class, 'patient_lead_id', 'id');
    }

    public function documents()
    {
        $response = $this->hasMany('App\PatientLeadDocument');
        if (!is_null(Auth::user()) && Auth::user()->isAdmin()) {
            $response->withoutGlobalScope(DocumentsForAllScope::class);
        }
        return $response;
    }

    public function faxes(): HasMany
    {
        return $this->hasMany(Fax::class);
    }

    public function therapyType()
    {
        return $this->belongsTo(PatientTherapyType::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('first_name', 'LIKE', "%{$search}%")
        ->orWhere('last_name', 'LIKE', "%{$search}%")
        ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ? ', "%{$search}%");
    }

    public function getFullName()
    {
        return implode(' ', [$this->first_name, $this->last_name]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function activeInquiry(): MorphOne
    {
        return $this->morphOne(PatientInquiry::class, 'inquirable')
            ->active()
            ->orderBy('created_at', 'DESC')
            ->limit(1);
    }

       /**
     * @param $value
     *
     * @return float|int
     */
    public function getSelfPayAttribute($value)
    {
        return $value / 100;
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setSelfPayAttribute($value)
    {
        $this->attributes['self_pay'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getVisitCopayAttribute($value)
    {
        return $value / 100;
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setVisitCopayAttribute($value)
    {
        $this->attributes['visit_copay'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleAttribute($value)
    {
        return $value / 100;
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleAttribute($value)
    {
        $this->attributes['deductible'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleMetAttribute($value)
    {
        return $value / 100;
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleMetAttribute($value)
    {
        $this->attributes['deductible_met'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleRemainingAttribute($value)
    {
        return $value / 100;
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleRemainingAttribute($value)
    {
        $this->attributes['deductible_remaining'] = intval(floatval($value) * 100);
    }

     /**
     * @param $value
     *
     * @return float|int
     */
    public function getInsurancePayAttribute($value)
    {
        return $value / 100;
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setInsurancePayAttribute($value)
    {
        $this->attributes['insurance_pay'] = intval(floatval($value) * 100);
    }
}
