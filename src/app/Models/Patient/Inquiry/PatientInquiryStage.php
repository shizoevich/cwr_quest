<?php

namespace App\Models\Patient\Inquiry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PatientInquiryStage extends Model
{
    public const STAGE_INBOX = 'Inbox';
    public const STAGE_IN_PROGRESS = 'In progress';
    public const STAGE_APPOINTMENT_SCHEDULED = 'Appointment scheduled';
    public const STAGE_ONBOARDING_COMPLETE = 'Onboarding complete';
    public const STAGE_INITIAL_APPOINTMENT_COMPLETE = 'Initial appointment complete';
    public const STAGE_INITIAL_SURVEY_COMPLETE = 'Initial survey complete';
    public const STAGE_FOUR_APPOINTMENTS_COMPLETE = '4 Appointments Complete';
    public const STAGE_ON_HOLD = 'On hold';

    public const COUNT_APPOINTMENTS_TO_SET_STAGE_FOUR_APPOINTMENTS_COMPLETE = 4;

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    public static function getInboxId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-inbox', function () {
            return self::where('name', self::STAGE_INBOX)->first()->id;
        });
    }

    public static function getInProgressId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-in_progress', function () {
            return self::where('name', self::STAGE_IN_PROGRESS)->first()->id;
        });
    }

    public static function getAppointmentScheduledId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-appointment_scheduled', function () {
            return self::where('name', self::STAGE_APPOINTMENT_SCHEDULED)->first()->id;
        });
    }

    public static function getOnboardingCompleteId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-onboarding_complete', function () {
            return self::where('name', self::STAGE_ONBOARDING_COMPLETE)->first()->id;
        });
    }

    public static function getInitialAppointmentCompleteId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-initial_appointment_complete', function () {
            return self::where('name', self::STAGE_INITIAL_APPOINTMENT_COMPLETE)->first()->id;
        });
    }

    public static function getInitialSurveyCompleteId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-initial_survey_complete', function () {
            return self::where('name', self::STAGE_INITIAL_SURVEY_COMPLETE)->first()->id;
        });
    }

    public static function getFourAppointmentsCompleteId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-four_appointments_complete', function () {
            return self::where('name', self::STAGE_FOUR_APPOINTMENTS_COMPLETE)->first()->id;
        });
    }

    public static function getOnHoldId(): int
    {
        return Cache::rememberForever('patient_inquiry_stage-on_hold', function () {
            return self::where('name', self::STAGE_ON_HOLD)->first()->id;
        });
    }

    public static function getStageIdsWithoutFormRequest(): array
    {
        return [
            self::getInboxId(),
            self::getInProgressId(),
        ];
    }
}
