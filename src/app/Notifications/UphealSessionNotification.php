<?php

namespace App\Notifications;

use App\Channels\Messages\RingcentralMessage;
use App\Models\UphealMeeting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UphealSessionNotification extends Notification
{
    use Queueable;

    protected $uphealMeeting;
    
    /**
     * Create a new notification instance.
     *
     * @param UphealMeeting $uphealMeeting
     */
    public function __construct(UphealMeeting $uphealMeeting)
    {
        $this->uphealMeeting = $uphealMeeting;
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
        if($this->uphealMeeting->appointment) {
            $formattedAppointmentTime = Carbon::createFromTimestamp($this->uphealMeeting->appointment->time)->format('m/d/Y h:i A');
        }
        
        return (new MailMessage)->view('emails.upheal.video-session', [
            'patient' => $this->uphealMeeting->patient,
            'provider' => $this->uphealMeeting->provider,
            'meeting' => $this->uphealMeeting,
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
        $messagePattern = 'Invitation to join Therapist%s @ CWR in a Telehealth session via Upheal%s: %s';
        $date = '';
        if ($this->uphealMeeting->appointment) {
            $date = ' (' . Carbon::createFromTimestamp($this->uphealMeeting->appointment->time)->format('m/d/Y h:i A') . ')';
        }
        $message = sprintf($messagePattern, ' ' . $this->uphealMeeting->provider->provider_name, $date, $this->uphealMeeting->patient->upheal_client_session_url);
        if (strlen($message) > 160) {
            $message = sprintf($messagePattern, '', $date, $this->uphealMeeting->patient->upheal_client_session_url);
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

    public function getMeetingId(): int
    {
        return $this->uphealMeeting->id;
    }

    public function getMeetingType()
    {
        return UphealMeeting::class;
    }
}
