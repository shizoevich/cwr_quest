<?php

namespace App\Components\SnoozedNotification;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use App\Exceptions\SnoozedNotification\LaravelSnoozeException;
use App\Exceptions\SnoozedNotification\NotificationAlreadySentException;
use App\Exceptions\SnoozedNotification\NotificationCancelledException;
use App\Exceptions\SnoozedNotification\SchedulingFailedException;
use App\Models\ScheduledNotification as ScheduledNotificationModel;

/**
 * @see https://github.com/thomasjohnkane/snooze
 * Class ScheduledNotification
 * @package App\Components\SnoozedNotification
 */
class ScheduledNotification
{
    /** @var ScheduledNotificationModel */
    private $scheduleNotificationModel;

    public function __construct(ScheduledNotificationModel $scheduleNotificationModel)
    {
        $this->scheduleNotificationModel = $scheduleNotificationModel;
    }

    /**
     * @param                   $notifiable
     * @param Notification      $notification
     * @param DateTimeInterface $sendAt
     *
     * @return self
     * @throws SchedulingFailedException
     */
    public static function create(
        $notifiable,
        Notification $notification,
        DateTimeInterface $sendAt
    ): self {
        if ($sendAt < Carbon::today()) {
            throw new SchedulingFailedException(sprintf('`send_at` must not be in the past: %s', $sendAt->format(DATE_ISO8601)));
        }

        if (! method_exists($notifiable, 'notify')) {
            throw new SchedulingFailedException(sprintf('%s is not notifiable', get_class($notifiable)));
        }

        $modelClass = self::getScheduledNotificationModelClass();

        $targetId = $notifiable instanceof Model ? $notifiable->getKey() : null;
        $targetType = $notifiable instanceof AnonymousNotifiable ? AnonymousNotifiable::class : get_class($notifiable);

        return new self($modelClass::create([
            'meeting_id' => $notification->getMeetingId(),
            'meeting_type' => $notification->getMeetingType(),
            'target_id' => $targetId,
            'target_type' => $targetType,
            'target' => Serializer::create()->serializeNotifiable($notifiable),
            'notification_type' => get_class($notification),
            'notification' => Serializer::create()->serializeNotification($notification),
            'send_at' => $sendAt,
        ]));
    }

    public static function find(int $scheduledNotificationId): ?self
    {
        $modelClass = self::getScheduledNotificationModelClass();

        $model = $modelClass::find($scheduledNotificationId);

        return $model ? new self($model) : null;
    }

    public static function findByType(string $notificationClass, bool $includeSent = false): Collection
    {
        $modelClass = self::getScheduledNotificationModelClass();

        if ($includeSent) {
            return self::collection($modelClass::whereNotificationType($notificationClass)->get());
        }

        return self::collection($modelClass::whereNotificationType($notificationClass)->whereNull('sent_at')->get());
    }

    public static function findByTarget($notifiable): ?Collection
    {
        if (! $notifiable instanceof Model) {
            return null;
        }

        $modelClass = self::getScheduledNotificationModelClass();

        $models = $modelClass::query()
            ->whereTargetId($notifiable->getKey())
            ->whereTargetType(get_class($notifiable))
            ->get();

        return self::collection($models);
    }

    public static function all(bool $includeSent = false, bool $includeCanceled = false): Collection
    {
        $modelClass = self::getScheduledNotificationModelClass();
        $query = $modelClass::query();

        if (! $includeSent) {
            $query->whereNull('sent_at');
        }

        if (! $includeCanceled) {
            $query->whereNull('cancelled_at');
        }

        return self::collection($query->get());
    }

    public static function cancelByTarget($notifiable): int
    {
        if (! $notifiable instanceof Model) {
            throw new LaravelSnoozeException(
                'Cannot cancel AnonymousNotifiable by instance. Use the `cancelAnonymousNotificationsByChannel` method instead');
        }

        $modelClass = self::getScheduledNotificationModelClass();

        return $modelClass::whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->whereTargetId($notifiable->getKey())
            ->whereTargetType(get_class($notifiable))
            ->update(['cancelled_at' => Carbon::now()]);
    }

    public static function cancelAnonymousNotificationsByChannel(string $channel, string $route): int
    {
        $modelClass = self::getScheduledNotificationModelClass();

        $notificationsToCancel = $modelClass::whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->whereTargetId(null)
            ->whereTargetType(AnonymousNotifiable::class)
            ->get()
            ->map(function (ScheduledNotificationModel $model) {
                return [
                    'id' => $model->id,
                    'routes' => Serializer::create()->unserializeNotifiable($model->target)->routes,
                ];
            })
            ->filter(function (array $item) use ($channel, $route) {
                // Check if the notifiable has a matching route for the specified channel
                return collect($item['routes'])->search($route, true) === $channel;
            })
            ->pluck('id');

        return $modelClass::whereIn('id', $notificationsToCancel)->update(['cancelled_at' => Carbon::now()]);
    }

    /**
     * @param DateTimeInterface|string $sendAt
     * @param bool                     $force
     *
     * @return self
     * @throws NotificationAlreadySentException
     * @throws NotificationCancelledException
     */
    public function reschedule($sendAt, $force = false): self
    {
        return new self($this->scheduleNotificationModel->reschedule($sendAt, $force));
    }

    /**
     * @param DateTimeInterface|string $sendAt
     *
     * @return self
     */
    public function scheduleAgainAt($sendAt): self
    {
        return new self($this->scheduleNotificationModel->scheduleAgainAt($sendAt));
    }

    public function cancel(int $reasonId = null): void
    {
        $this->scheduleNotificationModel->cancel($reasonId);
    }

    public function sendNow(): void
    {
        $this->scheduleNotificationModel->send();
    }

    public function refresh(): void
    {
        $this->scheduleNotificationModel->refresh();
    }

    public function isSent(): bool
    {
        return $this->scheduleNotificationModel->sent_at !== null;
    }

    public function isCancelled(): bool
    {
        return $this->scheduleNotificationModel->cancelled_at !== null;
    }

    public function isRescheduled(): bool
    {
        return $this->scheduleNotificationModel->rescheduled_at !== null;
    }

    public function getId()
    {
        return $this->scheduleNotificationModel->id;
    }

    public function getType()
    {
        return $this->scheduleNotificationModel->notification_type;
    }

    public function getTargetType()
    {
        return $this->scheduleNotificationModel->target_type;
    }

    public function getTargetId()
    {
        return $this->scheduleNotificationModel->target_id;
    }

    public function getSentAt()
    {
        return $this->scheduleNotificationModel->sent_at;
    }

    public function getCancelledAt()
    {
        return $this->scheduleNotificationModel->cancelled_at;
    }

    public function getRescheduledAt()
    {
        return $this->scheduleNotificationModel->rescheduled_at;
    }

    /**
     * @return Carbon
     */
    public function getSendAt(): Carbon
    {
        return $this->scheduleNotificationModel->send_at;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->scheduleNotificationModel->created_at;
    }

    /**
     * @return Carbon
     */
    public function getUpdatedAt(): Carbon
    {
        return $this->scheduleNotificationModel->updated_at;
    }

    public function getInterruptReasonId()
    {
        return $this->scheduleNotificationModel->getInterruptReasonId();
    }

    private static function getScheduledNotificationModelClass(): string
    {
        return config('snooze.model') ?? ScheduledNotificationModel::class;
    }

    private static function collection(Collection $models): Collection
    {
        return $models->map(function (ScheduledNotificationModel $model) {
            return new self($model);
        });
    }
}
