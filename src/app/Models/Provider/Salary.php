<?php

namespace App\Models\Provider;

use App\PatientVisit;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * \App\Models\Provider\Salary
 *
 * @property int $id
 * @property int $visit_id
 * @property int $provider_id
 * @property int $type See Salary Model Constants
 * @property int $fee
 * @property int $paid_fee
 * @property \Carbon\Carbon $date
 * @property int $new_record_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Provider $provider
 * @property-read \App\PatientVisit $visit
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Provider\Salary onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereNewRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary wherePaidFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\Salary whereVisitId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Provider\Salary withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Provider\Salary withoutTrashed()
 * @mixin \Eloquent
 */
class Salary extends Model
{
    use SoftDeletes;
    
    protected $table = 'salary';
    
    protected $fillable = [
        'visit_id',
        'provider_id',
        'type',
        'fee',
        'paid_fee',
        'date',
        'billing_period_id',
        'new_record_id',
        'deleted_at',
        'notes',
        'additional_data',
    ];
    
    protected $casts = [
        'visit_id' => 'int',
        'provider_id' => 'int',
        'type' => 'int',
        'fee' => 'int',
        'paid_fee' => 'int',
        'new_record_id' => 'int',
        'billing_period_id' => 'int',
        'additional_data' => 'array',
        'exist' => 'bool',
        'is_other' => 'bool',
    ];
    
    protected $dates = [
//        'date'
    ];
    
    /**
     * @var float
     * Pay provider for visit x1.5 rate
     */
    const OVERTIME_VISITS_RATE = 1.5;
    
    /**
     * % of the total late cancellation fee that the therapist will receive
     */
    const LATE_CANCELLATION_PROVIDER_PERCENTAGE = 60;
    
    /**
     * Amount in cents provider receive if attended monthly meeting
     */
    const MONTHLY_MEETING_ATTENDANCE_PRICE = 2500;
    
    /**
     * Amount in cents supervisor will receive for 1 hour of meeting with supervisee
     */
    const SUPERVISOR_HOUR_PRICE = 12500;

    /**
     * Amount in cents supervisee will receive for 1 hour of meeting with supervisor
     */
    const SUPERVISEE_HOUR_PRICE = 1800;
    
    /**
     * Amount in cents provider receive for 1 hour of sick time
     */
    const SICK_TIME_PRICE = 5400;
    
    /**
     * @var int
     */
    const TYPE_REGULAR_VISIT = 1;
    
    /**
     * Visit with reason "Telehealth"
     * @var int
     */
    const TYPE_TELEHEALTH_VISIT = 2;
    
    /**
     * Provider not complete progress note for appointment
     * @var int
     */
    const TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE = 3;
    
    /**
     * Provider not complete progress note for appointment (for Telehealth session)
     * @var int
     */
    const TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE = 4;
    
    /**
     * Provider complete progress note for appointment from past billing period
     * @var int
     */
    const TYPE_REFUND_FOR_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE = 5;
    
    /**
     * Provider complete progress note for appointment from past billing period (for Telehealth session)
     * @var int
     */
    const TYPE_REFUND_FOR_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE = 6;
    
    /**
     * Pay provider for monthly meeting attendance
     * @var int
     */
    const TYPE_MONTHLY_MEETING_ATTENDANCE_COMPENSATION = 7;
    
    /**
     * Pay provider for overtime
     * @var int
     */
    const TYPE_OVERTIME_COMPENSATION = 8;
    
    /**
     * Pay supervisor for work with supervisee
     * @var int
     */
    const TYPE_SUPERVISOR_COMPENSATION = 9;

    /**
     * Pay supervisee for work with supervisor
     * @var int
     */
    const TYPE_SUPERVISEE_COMPENSATION = 15;
    
    /**
     * Pay provider for late appointment cancellation by patient
     * @var int
     */
    const TYPE_LATE_APPT_CANCELLATION_FEE = 10;
    
    /**
     * @var int
     */
    const TYPE_OTHER_COMPENSATION = 11;
    
    /**
     * @var int
     */
    const TYPE_CREATED_FROM_TIMESHEET_VISIT = 12;
    
    /**
     * @var int
     */
    const TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION = 13;
    
    /**
     * @var int
     */
    const TYPE_SICK_TIME = 14;

    /**
     * @var int
     */
    const TYPE_TRAINING = 16;
    
    const TELEHEALTH_TYPES = [
        self::TYPE_TELEHEALTH_VISIT,
        self::TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE,
        self::TYPE_REFUND_FOR_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE,
    ];
    
    const MISSING_PROGRESS_NOTE_TYPES = [
        self::TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE,
        self::TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE,
    ];
    
    const REFUND_TYPES = [
        self::TYPE_REFUND_FOR_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE,
        self::TYPE_REFUND_FOR_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE,
    ];
    
    const ADDITIONAL_COMPENSATION_TYPES = [
        // 'overtime' => self::TYPE_OVERTIME_COMPENSATION,
        // 'late_appt_cancellation' => self::TYPE_LATE_APPT_CANCELLATION_FEE,

        'monthly_meeting_attendance' => self::TYPE_MONTHLY_MEETING_ATTENDANCE_COMPENSATION,
        'supervisor_compensation' => self::TYPE_SUPERVISOR_COMPENSATION,
        'supervisee_compensation' => self::TYPE_SUPERVISEE_COMPENSATION,
        'sick_time' => self::TYPE_SICK_TIME,
        'training' => self::TYPE_TRAINING,
        'other' => self::TYPE_OTHER_COMPENSATION,
    ];
    
    const CREATED_FROM_TIMESHEET_TYPES = [
        self::TYPE_CREATED_FROM_TIMESHEET_VISIT,
        self::TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION,
    ];
    
    /**
     * @param $type
     *
     * @return string|null
     */
    public static function getTitleByType($type): ?string
    {
        switch ($type) {
            case self::TYPE_MONTHLY_MEETING_ATTENDANCE_COMPENSATION:
                return 'Monthly Meeting Attendance';
            case self::TYPE_OVERTIME_COMPENSATION:
                return 'Overtime Compensation';
            case self::TYPE_SUPERVISOR_COMPENSATION:
                return 'Supervisor Compensation';
            case self::TYPE_SUPERVISEE_COMPENSATION:
                return 'Supervisee Compensation';
            case self::TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION:
            case self::TYPE_LATE_APPT_CANCELLATION_FEE:
                return 'Late Appt. Cancellation';
            case self::TYPE_OTHER_COMPENSATION:
                return 'Other Fees';
            case self::TYPE_SICK_TIME:
                return 'Sick Time';
            case self::TYPE_TRAINING:
                return 'Training';
            default:
                return null;
        }
    }
    
    /**
     * @param $type
     *
     * @return string|null
     */
    public static function getSlugByType($type): ?string
    {
        foreach (self::ADDITIONAL_COMPENSATION_TYPES as $slug => $item) {
            if($item == $type) {
                return $slug;
            }
        }
        
        return null;
    }
    
    /**
     * @return BelongsTo
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class);
    }
    
    /**
     * @return BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
    
    public function getFeeAttribute($value)
    {
        return $value / 100;
    }
    
    public function getPaidFeeAttribute($value)
    {
        return $value / 100;
    }
    
    /**
     * @param array $newData
     *
     * @return Model
     */
    public function updateSalaryRecord(array $newData)
    {
        $newRecord = $this->replicate([
            'created_at',
            'updated_at',
            'deleted_at',
            'new_record_id',
        ]);
        $dataChanged = false;
        foreach ($newData as $key => $item) {
            if($this->getOriginal($key) != $item) {
                $newRecord->setAttribute($key, $item);
                $dataChanged = true;
            }
        }
        if($dataChanged) {
            $newRecord = static::query()->updateOrCreate($newRecord->getAttributes());
//            $newRecord->save();
            if(in_array($newRecord->type, self::REFUND_TYPES) && !in_array($this->type, self::REFUND_TYPES) && $this->getOriginal('billing_period_id') != $newRecord->getOriginal('billing_period_id')) {
                return $newRecord;
            }
    
            if(in_array($newRecord->type, self::MISSING_PROGRESS_NOTE_TYPES) && in_array($this->type, self::REFUND_TYPES) && $this->getOriginal('billing_period_id') != $newRecord->getOriginal('billing_period_id')) {
                return $newRecord;
            }
            
            if(in_array($newRecord->type, [self::TYPE_REGULAR_VISIT, self::TYPE_TELEHEALTH_VISIT])) {
                self::query()
                    ->where('visit_id', $newRecord->visit_id)
                    ->where('id', '!=', $newRecord->getKey())
                    ->update([
                        'new_record_id' => $newRecord->getKey(),
                        'deleted_at' => Carbon::now(),
                    ]);
            } else {
                $this->update([
                    'new_record_id' => $newRecord->getKey(),
                    'deleted_at' => Carbon::now(),
                ]);
            }
            
            return $newRecord;
        }
        
        return $this;
    }
    
    /**
     * @param $type
     *
     * @return bool
     */
    public static function isTelehealth($type): bool
    {
        return in_array($type, self::TELEHEALTH_TYPES);
    }
    
    /**
     * @param $type
     *
     * @return bool
     */
    public static function isProgressNoteMissing($type): bool
    {
        return in_array($type, self::MISSING_PROGRESS_NOTE_TYPES);
    }
    
    /**
     * @param $type
     *
     * @return bool
     */
    public static function isRefund($type): bool
    {
        return in_array($type, self::REFUND_TYPES);
    }
    
    /**
     * @param $type
     *
     * @return bool
     */
    public static function isAdditionalCompensation($type): bool
    {
        return in_array($type, self::ADDITIONAL_COMPENSATION_TYPES);
    }
}
