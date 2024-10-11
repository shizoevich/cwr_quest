<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Status
 *
 * @property int $id
 * @property int $external_id
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Appointment[] $appointments
 * @method static Builder|Status otherCancelStatuses()
 * @method static Builder|Status whereCreatedAt($value)
 * @method static Builder|Status whereId($value)
 * @method static Builder|Status whereStatus($value)
 * @method static Builder|Status whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Status extends Model
{
    protected $table = 'appointment_statuses';

    protected $fillable = ['status', 'external_id'];

    protected $casts = [
        'external_id' => 'int',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'appointment_statuses_id', 'id');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeOtherCancelStatuses($query)
    {
        return $query->whereIn('status', [
            'Rescheduled',
            'Patient Did Not Come',
            'Last Minute Reschedule',
        ]);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeRescheduleStatuses($query)
    {
        return $query->whereIn('status', [
            'Rescheduled',
            'Last Minute Reschedule',
        ]);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNewOtherCancelStatuses($query)
    {
        return $query->whereIn('status', [
            'Patient Did Not Come',
        ]);
    }

    /**
     * @return mixed
     */
    public static function getVisitCreatedId()
    {
        return \Cache::rememberForever('appointment_statuses:visit_created', function () {
            return static::where('status', 'Visit Created')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getActiveId()
    {
        return \Cache::rememberForever('appointment_statuses:active', function () {
            return static::where('status', 'Active')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getCompletedId()
    {
        return \Cache::rememberForever('appointment_statuses:completed', function () {
            return static::where('status', 'Completed')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getRescheduledId()
    {
        return \Cache::rememberForever('appointment_statuses:rescheduled', function () {
            return static::where('status', 'Rescheduled')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getLastMinuteRescheduleId()
    {
        return \Cache::rememberForever('appointment_statuses:last_minute_reschedule', function () {
            return static::where('status', 'Last Minute Reschedule')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getCancelledByPatientId()
    {
        return \Cache::rememberForever('appointment_statuses:cancelled_by_patient', function () {
            return static::where('status', 'Cancelled by Patient')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getCancelledByProviderId()
    {
        return \Cache::rememberForever('appointment_statuses:cancelled_by_provider', function () {
            return static::where('status', 'Cancelled by Provider')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getLastMinuteCancelByPatientId()
    {
        return \Cache::rememberForever('appointment_statuses:last_minute_cancel_by_patient', function () {
            return static::where('status', 'Last Minute Cancel by Patient')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getPatientDidNotComeId()
    {
        return \Cache::rememberForever('appointment_statuses:patient_did_not_come', function () {
            return static::where('status', 'Patient Did Not Come')->first()['id'];
        });
    }

    /**
     * @return mixed
     */
    public static function getCancelledByOfficeId()
    {
        return \Cache::rememberForever('appointment_statuses:cancelled_by_office', function () {
            return static::where('status', 'Cancelled by Office')->first()['id'];
        });
    }

    public static function getStatusesIdLikeCancel(): array
    {
        return static::select(['id'])
            ->where('status', 'like', '%cancel%')
            ->get()
            ->pluck('id')
            ->toArray();
    }

    public static function getOtherCancelStatusesId(): array
    {
        return \Cache::rememberForever('appointment_statuses:other_cancel_status_ids', function () {
            return static::select(['id'])
                ->otherCancelStatuses()
                ->orWhere('status', 'like', '%cancel%')
                ->pluck('id')
                ->toArray();
        });
    }

    public static function getStatusesLikeCancel(): array
    {
        return static::select(['id', 'status'])
            ->where('status', 'like', '%cancel%')
            ->orderBy('status')
            ->get()
            ->toArray();
    }

    public static function getOtherCancelStatuses(array $select = ['id', 'status']): array
    {
        return static::select($select)
            ->otherCancelStatuses()
            ->orWhere('status', 'like', '%cancel%')
            ->orderBy('status')
            ->get()
            ->toArray();
    }

    public static function getNewCancelStatuses(array $select = ['id', 'status']): array
    {
        return static::select($select)
            ->newOtherCancelStatuses()
            ->orWhere('status', 'like', '%cancel%')
            ->orderBy('status')
            ->get()
            ->toArray();
    }

    public static function getNewCancelStatusesId(): array
    {
        return \Cache::rememberForever('appointment_statuses:new_cancel_status_ids', function () {
            return static::select(['id'])
                ->newOtherCancelStatuses()
                ->orWhere('status', 'like', '%cancel%')
                ->pluck('id')
                ->toArray();
        });
    }

    public static function getRescheduleStatuses(array $select = ['id', 'status']): array
    {
        return \Cache::rememberForever('appointment_statuses:reschedule_statuses', function () use ($select) {
            return static::select($select)
                ->rescheduleStatuses()
                ->get()
                ->toArray();
        });
    }

    public static function getRescheduleStatusesId(): array
    {
        return \Cache::rememberForever('appointment_statuses:reschedule_status_ids', function () {
            return static::select(['id'])
                ->rescheduleStatuses()
                ->pluck('id')
                ->toArray();
        });
    }

    /**
     * @return mixed
     */
    public static function getCompletedVisitCreatedStatusesId()
    {
        return \Cache::rememberForever('appointment_statuses:completed_visit_created_status_ids', function () {
            return [
                Status::getCompletedId(),
                Status::getVisitCreatedId(),
            ];
        });
    }

    public static function getActiveCompletedVisitCreatedStatusesId()
    {
        return \Cache::rememberForever('appointment_statuses:active_completed_visit_created_status_ids', function () {
            return [
                Status::getActiveId(),
                Status::getCompletedId(),
                Status::getVisitCreatedId(),
            ];
        });
    }

    public static function getStatusesForCancellationFee()
    {
        return \Cache::rememberForever('statuses_for_cancellation_fee', function () {
            return [
                Status::getLastMinuteCancelByPatientId(),
                Status::getPatientDidNotComeId(),
            ];
        });
    }
}
