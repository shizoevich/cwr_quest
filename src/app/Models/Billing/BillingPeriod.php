<?php

namespace App\Models\Billing;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * App\Models\Billing\BillingPeriod
 *
 * @property int $id
 * @property int $type_id
 * @property string $start_date
 * @property string $end_date
 * @property-read \App\Models\Billing\BillingPeriodType $type
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPeriod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPeriod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPeriod query()
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPeriod whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPeriod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPeriod whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPeriod whereTypeId($value)
 * @mixin \Eloquent
 */
class BillingPeriod extends Model
{
    public $timestamps = false;
    
    const DEFAULT_START_DATE = '2020-06-22';
    
    protected $fillable = [
        'type_id',
        'start_date',
        'end_date',
    ];
    
    /**
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(BillingPeriodType::class, 'type_id');
    }
    
    public static function getPrevious(string $type)
    {
        return self::query()
            ->select('billing_periods.*')
            ->join('billing_period_types', 'billing_period_types.id', 'billing_periods.type_id')
            ->whereDate('billing_periods.end_date', '<', Carbon::today()->toDateString())
            // ->whereDate('billing_periods.end_date', '<', Carbon::today()->addDays(2)->toDateString())
            ->where('billing_period_types.name', $type)
            ->orderByDesc('billing_periods.end_date')
            ->first();
    }
    
    public static function getCurrent(string $type)
    {
        return self::query()
            ->select('billing_periods.*')
            ->join('billing_period_types', 'billing_period_types.id', 'billing_periods.type_id')
            ->whereDate('billing_periods.start_date', '<=', Carbon::today()->toDateString())
            ->where('billing_period_types.name', $type)
            ->orderByDesc('billing_periods.start_date')
            ->first();
    }
    
    public static function getBillingPeriodByDate(Carbon $date, int $billingPeriodTypeId)
    {
        return \Cache::remember("billing_period_{$date->toDateString()}_{$billingPeriodTypeId}", 5, function() use ($date, $billingPeriodTypeId) {
            return self::query()
                ->where('billing_periods.end_date', '>=', $date->toDateString())
                ->where('billing_periods.start_date', '<=', $date->toDateString())
                ->where('billing_periods.type_id', $billingPeriodTypeId)
                ->orderByDesc('start_date')
                ->first();
        });
    }
}
