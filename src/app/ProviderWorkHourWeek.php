<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProviderWorkHourWeek
 *
 * @property int $id
 * @property int $provider_id
 * @property int $year
 * @property int $week
 * @property int $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereProvider($providerId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHourWeek whereYear($value)
 * @mixin \Eloquent
 */
class ProviderWorkHourWeek extends Model
{
    protected $table = 'provider_work_hours_weeks';

    protected $guarded = [];

    protected $fillable = [
        'provider_id',
        'year',
        'week',
        'status'
    ];
    protected $casts = [
        'provider_id' => 'integer',
        'year' => 'integer',
        'week' => 'integer',
        'status' => 'integer'
    ];

    public function provider()
    {
        return $this->belongsTo('App\Provider', 'provider_id', 'id');
    }

    public function scopeWhereProvider($query, $providerId)
    {
        if($providerId == 0) {
            return $query;
        } else {
            return $query->where('provider_id', $providerId);
        }
    }
}
