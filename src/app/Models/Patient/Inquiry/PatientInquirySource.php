<?php

namespace App\Models\Patient\Inquiry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PatientInquirySource extends Model
{
    protected $fillable = [
        'name',
        'channel_id'
    ];

    public $timestamps = false;

    public function channel()
    {
        return $this->belongsTo(PatientInquiryChannel::class, 'channel_id', 'id');
    }

    public static function getInsuranceCompId(): int
    {
        return Cache::rememberForever('patient-inquiry-source:insurance-comp-id', function (){
            return self::query()
                ->where('name', 'Insurance comp')
                ->where('channel_id', PatientInquiryChannel::getReferralId())
                ->first()
                ->id;
        });
    }
}
