<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \App\Models\Diagnose
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property int $hcc
 * @property int $is_billable
 * @property \Carbon\Carbon|null $terminated_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereHcc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereIsBillable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereTerminatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Diagnose whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Diagnose extends Model
{
    protected $appends = ['full_name'];
    
    protected $fillable = [
        'code',
        'description',
        'hcc',
        'is_billable',
        'terminated_at',
        'is_custom',
    ];
    
    protected $dates = [
        'terminated_at'
    ];
    
    protected $casts = [
        'hcc' => 'bool',
        'is_billable' => 'bool',
        'is_custom' => 'bool',
    ];
    
    public function getFullNameAttribute()
    {
        return $this->code . ' - ' . $this->description;
    }
    
    /**
     * @param array $diagnoseIds
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getDiagnosesSavingOrder(array $diagnoseIds)
    {
        $diagnoseIds = array_unique($diagnoseIds);
        
        $diagnoses = Diagnose::query()
            ->whereKey($diagnoseIds)
            ->get(['id', 'code', 'description']);
    
        /**
         * this code needs for saving user select ordering
         */
        $preparedDiagnoses = collect();
        foreach ($diagnoseIds as $diagnoseId) {
            $preparedDiagnoses->push($diagnoses->where('id', '=', $diagnoseId)->first());
        }
        
        return $preparedDiagnoses;
    }
    
    public function scopeActive($query)
    {
        return $query->whereNull($this->getTable() . '.terminated_at')->where($this->getTable() . '.is_custom', 0);
    }
}
