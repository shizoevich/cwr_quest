<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Office
 *
 * @property int $id
 * @property int $external_id
 * @property string|null $office
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Appointment[] $appointments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeRoom[] $rooms
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Office whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Office whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Office whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Office whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Office extends Model
{
    protected $table = 'offices';

    protected $fillable = ['office', 'external_id'];

    protected $casts = [
        'external_id' => 'int',
        'office' => 'string',
        'tridiuum_site_id' => 'string',
        'tridiuum_is_enabled' => 'bool'
    ];

    public function appointments()
    {
        return $this->hasMany('App\Appointment', 'offices_id', 'id');
    }

    public function rooms()
    {
        return $this->hasMany('App\OfficeRoom', 'office_id', 'id');
    }

    public static function getEncinoOffice(): Office
    {
        return Cache::rememberForever('offices:encino-office', function () {
            return static::where('office', 'Encino Office')->first();
        });
    }
}
