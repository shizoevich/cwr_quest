<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use App\Models\GoogleMeeting;
use App\Channels\RingcentralSmsChannel;
use App\Channels\Messages\RingcentralMessage;
use App\Models\ScheduledNotification;
use App\Status;

class AppointmentNotification2 extends Notification
{
    use Queueable;

    protected $googleMeeting;
    
    /**
     * Create a new notification instance.
     *
     * @param GoogleMeeting $googleMeeting
     */
    public function __construct(GoogleMeeting $googleMeeting)
    {
        $this->googleMeeting = $googleMeeting;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [RingcentralSmsChannel::class];
    }

    /**
     * @param $notifiable
     * @return RingcentralMessage
     */
    public function toRingcentralMessage($notifiable)
    {   
        return (new RingcentralMessage())->content($this->getSmsMessage());
    }

    /**
     * @return string
     */
    private function getSmsMessage(): string
    {
        return sprintf('To join your Telehealth session by phone, please dial %s and enter this PIN code: %s#', $this->googleMeeting->conference_phone, $this->googleMeeting->conference_phone_pin);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function getInterruptReasonId()
    {
        $reasonId = null;
        $appointment = optional($this->googleMeeting)->appointment;

        if (empty($appointment) || $appointment->appointment_statuses_id !== Status::getActiveId()) {
            $reasonId = ScheduledNotification::HAS_NOT_ACTIVE_APPOINTMENT_REASON_ID;
        } else if (empty($this->googleMeeting->conference_uri)) {
            $reasonId = ScheduledNotification::HAS_NOT_GOOGLE_MEETING_URL_REASON_ID;
        }

        return $reasonId;
    }

    public function getMeetingId(): int
    {
        return $this->googleMeeting->id;
    }

    public function getMeetingType()
    {
        return GoogleMeeting::class;
    }
}
