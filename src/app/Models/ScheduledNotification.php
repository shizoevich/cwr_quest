<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Events\SnoozedNotification\NotificationInterrupted;
use App\Events\SnoozedNotification\NotificationSent;
use App\Exceptions\SnoozedNotification\NotificationAlreadySentException;
use App\Exceptions\SnoozedNotification\NotificationCancelledException;
use App\Components\SnoozedNotification\Serializer;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @see https://github.com/thomasjohnkane/snooze
 * Class ScheduledNotification
 * @package App\Models
 */
class ScheduledNotification extends Model
{
    use SoftDeletes;

    const HAS_NOT_ACTIVE_APPOINTMENT_REASON_ID = 1;

    const HAS_NOT_GOOGLE_MEETING_URL_REASON_ID = 2;

    const EMAIL_NOT_SENT_EXCEPTION_REASON_ID = 3;

    /** @var string */
    protected $table;
    /** @var Serializer */
    protected $serializer;

    protected $dates = [
        'send_at',
        'sent_at',
        'rescheduled_at',
        'cancelled_at',
    ];

    protected $fillable = [
        'meeting_id',
        'meeting_type',
        'target_id',
        'target_type',
        'target',
        'notification_type',
        'notification',
        'send_at',
        'sent',
        'rescheduled',
        'cancelled_at',
        'cancellation_reason_id',
        'created_at',
        'updated_at',
    ];

    protected $attributes = [
        'sent_at' => null,
        'rescheduled_at' => null,
        'cancelled_at' => null,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('snooze.snooze_table');
        $this->serializer = Serializer::create();
    }

    public function send(): void
    {
        if ($this->cancelled_at !== null) {
            throw new NotificationCancelledException('Cannot Send. Notification cancelled.', 1);
        }

        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Send. Notification already sent.', 1);
        }

        $notifiable = $this->serializer->unserializeNotifiable($this->target);
        $notification = $this->serializer->unserializeNotification($this->notification);

        $interruptReasonId = $this->getInterruptReasonId($notification, $notifiable);
        if (isset($interruptReasonId)) {
            $this->cancel($interruptReasonId);
            event(new NotificationInterrupted($this));

            return;
        }

        $notifiable->notify($notification);

        event(new NotificationSent($this));

        $this->sent_at = Carbon::now();
        $this->save();
    }

    /**
     * @param $notification
     *
     * @param $notifiable
     *
     * @return bool
     */
    public function getInterruptReasonId($notification = null, $notifiable = null)
    {
        if (! $notification) {
            $notification = $this->serializer->unserializeNotification($this->notification);
        }

        if (! $notifiable) {
            $notifiable = $this->serializer->unserializeNotifiable($this->target);
        }

        if (method_exists($notification, 'getInterruptReasonId')) {
            return $notification->getInterruptReasonId($notifiable);
        }

        return null;
    }

    /**
     * @return void
     * @throws NotificationAlreadySentException
     */
    public function cancel(int $reasonId = null): void
    {
        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Cancel. Notification already sent.', 1);
        }

        $this->cancelled_at = Carbon::now();
        $this->cancellation_reason_id = $reasonId;
        $this->save();
    }

    /**
     * @param \DateTimeInterface|string $sendAt
     * @param bool                      $force
     *
     * @return self
     * @throws NotificationAlreadySentException
     * @throws NotificationCancelledException
     */
    public function reschedule($sendAt, $force = false): self
    {
        if (! $sendAt instanceof \DateTimeInterface) {
            $sendAt = Carbon::parse($sendAt);
        }

        if (($this->sent_at !== null || $this->cancelled_at !== null) && $force) {
            return $this->scheduleAgainAt($sendAt);
        }

        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Reschedule. Notification Already Sent', 1);
        }

        if ($this->cancelled_at !== null) {
            throw new NotificationCancelledException('Cannot Reschedule. Notification cancelled.', 1);
        }

        $this->send_at = $sendAt;
        $this->rescheduled_at = Carbon::now();
        $this->save();

        return $this;
    }

    /**
     * @param \DateTimeInterface|string $sendAt
     *
     * @return self
     */
    public function scheduleAgainAt($sendAt): self
    {
        if (! $sendAt instanceof \DateTimeInterface) {
            $sendAt = Carbon::parse($sendAt);
        }

        $notification = $this->replicate();

        $notification->fill([
            'send_at' => $sendAt,
            'sent_at' => null,
            'rescheduled_at' => null,
            'cancelled_at' => null,
        ]);

        $notification->save();

        return $notification;
    }
}
