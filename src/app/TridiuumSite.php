<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TridiuumSite extends Model
{
    protected $table = 'tridiuum_sites';
    protected $fillable = ['tridiuum_site_id', 'tridiuum_site_name'];

    public function appointments()
    {
        return $this->hasMany(KaiserAppointment::class, 'site_id');
    }
}
