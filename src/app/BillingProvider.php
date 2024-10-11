<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\BillingProvider
 *
 * @property string $id
 * @property string $name
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $group_no
 * @property string $tax_id
 * @property string $npi
 * @property string $phone
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereGroupNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereNpi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BillingProvider whereZip($value)
 * @mixin \Eloquent
 */
class BillingProvider extends Model
{
    protected $dates = ['created_at', 'updated_at'];

    protected $guarded = [];

    public $incrementing = false;

    protected $primaryKey = 'id';
}
