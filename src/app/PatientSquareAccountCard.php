<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientSquareAccountCard
 *
 * @property int $id
 * @property int $patient_square_account_id
 * @property string $card_nonce
 * @property string $card_id
 * @property string|null $card_brand
 * @property string|null $last_four
 * @property int|null $exp_month
 * @property int|null $exp_year
 * @property string|null $cardholder_name
 * @property string|null $address_line_one
 * @property string|null $address_line_two
 * @property string|null $locality
 * @property string|null $administrative_district_level_one
 * @property string|null $postal_code
 * @property string|null $country
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\PatientSquareAccount $account
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereAddressLineOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereAddressLineTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereAdministrativeDistrictLevelOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereCardNonce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereCardholderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereExpMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereExpYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereLocality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard wherePatientSquareAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccountCard whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientSquareAccountCard extends Model
{
    protected $fillable = [
        'card_nonce',
        'card_id',
        'card_brand',
        'exp_month',
        'exp_year',
        'cardholder_name',
        'address_line_one',
        'address_line_two',
        'locality',
        'administrative_district_level_one',
        'postal_code',
        'country',
        'last_four',
    ];

    public function account()
    {
        return $this->belongsTo(PatientSquareAccount::class, 'patient_square_account_id', 'id');
    }
    
    /**
     * @return bool
     */
    public function getIsExpiredAttribute()
    {
        $expirationDate = Carbon::createFromFormat('Y-n', "{$this->exp_year}-{$this->exp_month}")->endOfMonth();
        
        return Carbon::now()->gt($expirationDate);
    }

    public function getDirtyWithOriginal()
    {
        $result = [];
        $dirtyFields = $this->getDirty();

        foreach ($dirtyFields as $fieldName => $fieldValue) {
            $result[$fieldName] = [
                'prev' => $this->getOriginal($fieldName),
                'curr' => $fieldValue,
            ];
        }

        return $result;
    }

    public function getLogData()
    {
        return [
            'id' => $this->id,
            'patient_square_account_id' => $this->patient_square_account_id,
            'card_nonce' => $this->card_nonce,
            'card_id' => $this->card_id,
            'card_brand' => $this->card_brand,
            'last_four' => $this->last_four,
            'exp_month' => $this->exp_month,
            'exp_year' => $this->exp_year,
            'cardholder_name' => $this->cardholder_name,
            'address_line_one' => $this->address_line_one,
            'address_line_two' => $this->address_line_two,
            'locality' => $this->locality,
            'administrative_district_level_one' => $this->administrative_district_level_one,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Card created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (empty($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
            }
        }

        return 'Card updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Card deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_square_account_id}; {$this->card_id};"
            . Carbon::parse($this->created_at)->toDateTimeString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_square_account_id' => 'Patient square account id',
            'card_nonce' => 'Card nonce',
            'card_id' => 'Card id',
            'card_brand' => 'Card brand',
            'last_four' => 'Last four',
            'exp_month' => 'Expiration month',
            'exp_year' => 'Expiration year',
            'cardholder_name' => 'Cardholder name',
            'address_line_one' => 'Address line one',
            'address_line_two' => 'Address line two',
            'locality' => 'Locality',
            'administrative_district_level_one' => 'Administrative district level one',
            'postal_code' => 'Postal code',
            'country' => 'Country',
        ];
    }
}
