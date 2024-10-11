<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use App\Models\GoogleMeeting;
use App\Channels\Messages\RingcentralMessage;
use App\Models\ScheduledNotification;
use App\Status;
use Carbon\Carbon;

class AppointmentNotification extends Notification
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
        return $notifiable->getChannels();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable)
    {
        $formattedAppointmentTime = '';
        if ($this->googleMeeting->appointment) {
            $formattedAppointmentTime = Carbon::createFromTimestamp($this->googleMeeting->appointment->time)->format('m/d/Y h:i A');
        }
        
        return (new MailMessage)->view('emails.google.video-session', [
            'patient' => $this->googleMeeting->patient,
            'provider' => $this->googleMeeting->provider,
            'meeting' => $this->googleMeeting,
            'formattedAppointmentTime' => $formattedAppointmentTime,
        ]);
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
        $messagePattern = 'Invitation to join Therapist%s @ CWR in a Telehealth session via Google Meet%s: %s';
        $date = '';
        if ($this->googleMeeting->appointment) {
            $date = ' (' . Carbon::createFromTimestamp($this->googleMeeting->appointment->time)->format('m/d/Y h:i A') . ')';
        }
        $message = sprintf($messagePattern, ' ' . $this->googleMeeting->provider->provider_name, $date, $this->googleMeeting->conference_uri);
        if (strlen($message) > 160) {
            $message = sprintf($messagePattern, '', $date, $this->googleMeeting->conference_uri);
        }

        return $message;
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
