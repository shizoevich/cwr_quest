<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    const CARRIER_TYPE_MOBILE = 'mobile';
    const CARRIER_TYPE_VOIP = 'voip';
    
    const COUNTRY_CODE_US = 'US';
    
    protected $fillable = [
        'sanitized_phone',
        'phone',
        'country_code',
        'carrier_type',
        'carrier_name',
    ];
    
    /**
     * @return bool
     */
    public function isSmsAllowed(): bool
    {
        return ($this->carrier_type === self::CARRIER_TYPE_MOBILE || $this->carrier_type === self::CARRIER_TYPE_VOIP) && $this->country_code === self::COUNTRY_CODE_US;
    }
}
