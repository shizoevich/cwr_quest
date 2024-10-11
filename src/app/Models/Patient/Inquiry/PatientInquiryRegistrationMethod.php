<?php

namespace App\Models\Patient\Inquiry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PatientInquiryRegistrationMethod extends Model
{
    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    public static function getFaxId(): int
    {
        return Cache::rememberForever('patient-inquiry-registration-method:fax-id', function (){
            return self::where('name', 'Fax')->first()->id;
        });
    }
}
