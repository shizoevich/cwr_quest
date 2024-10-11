<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * \App\Models\Billing\BillingPeriodType
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Billing\BillingPeriod[] $periods
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Billing\BillingPeriodType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Billing\BillingPeriodType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Billing\BillingPeriodType whereTitle($value)
 * @mixin \Eloquent
 */
class BillingPeriodType extends Model
{
    const TYPE_BI_WEEKLY = 'bi_weekly';
    
    const TYPE_MONTHLY = 'monthly';
    
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'title'
    ];
    
    /**
     * @return HasMany
     */
    public function periods()
    {
        return $this->hasMany(BillingPeriod::class, 'type_id');
    }
    
    public static function getFromCache()
    {
        return \Cache::rememberForever('billing_period_types', function() {
            return static::all();
        });
    }
    
    public static function getBiWeekly()
    {
        $types = static::getFromCache();
        
        return $types->where('name', '=', static::TYPE_BI_WEEKLY)->first();
    }

    public static function getMonthly()
    {
        $types = static::getFromCache();
        
        return $types->where('name', '=', static::TYPE_MONTHLY)->first();
    }
}
