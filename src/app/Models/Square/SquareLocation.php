<?php

namespace App\Models\Square;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Square\SquareLocation
 *
 * @property int $id
 * @property string $external_id
 * @property string|null $name
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $locality
 * @property string|null $administrative_district_level_1
 * @property string|null $postal_code
 * @property string|null $country
 * @property string|null $merchant_id
 * @property string|null $currency
 * @property string|null $phone_number
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Square\SquareTransaction[] $transactions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereAdministrativeDistrictLevel1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereLocality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareLocation wherePostalCode($value)
 * @mixin \Eloquent
 */
class SquareLocation extends Model
{
    protected $table = 'square_locations';

    public $timestamps = false;

    protected $guarded = [];

    public static function getIds() {
        return static::pluck('external_id')->toArray();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions() {
        return $this->hasMany(SquareTransaction::class, 'location_id', 'id');
    }
}
