<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProviderInsurance
 *
 * @property int $provider_id
 * @property int $insurance_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInsurance whereInsuranceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInsurance whereProviderId($value)
 * @mixin \Eloquent
 */
class ProviderInsurance extends Model {
    protected $table = 'provider_insurances';

    public $timestamps = false;

    public $incrementing = false;

    protected $guarded = [];
}
