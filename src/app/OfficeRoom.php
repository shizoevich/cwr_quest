<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\OfficeRoom
 *
 * @property int $id
 * @property string $name
 * @property int $office_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Appointment[] $appointments
 * @property-read \App\Office $office
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeRoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeRoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeRoom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeRoom whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeRoom whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OfficeRoom extends Model
{
    protected $table = 'office_rooms';

    protected $fillable = [
        'office_id',
        'name',
        'external_id',
    ];

    public function office()
    {
        return $this->belongsTo('App\Office', 'office_id', 'id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'office_room_id', 'id');
    }
}
