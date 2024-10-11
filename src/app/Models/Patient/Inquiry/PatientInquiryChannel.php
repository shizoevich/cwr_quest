<?php

namespace App\Models\Patient\Inquiry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PatientInquiryChannel extends Model
{
    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    public static function getReferralId() : int
    {
        return Cache::rememberForever('patient-inquiry-channel:referral-id', function() {
            return self::where('name', 'Referral')->first()->id;
        });
    }

    public static function getOtherId() : int
    {
        return Cache::rememberForever('patient-inquiry-channel:other-id', function() {
            return self::where('name', 'Other')->first()->id;
        });
    }
}
