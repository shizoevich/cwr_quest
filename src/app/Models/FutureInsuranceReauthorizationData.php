<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FutureInsuranceReauthorizationData extends Model
{
    protected $table = 'future_insurance_reauthorization_data';

    protected $fillable = [
        'form_id',
        'auth_number',
        'visits_auth',
        'eff_start_date',
        'eff_stop_date'
    ];
}
