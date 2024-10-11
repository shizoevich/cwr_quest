<?php

namespace App\Traits\Appointments;

use App\Appointment;
use App\Components\SnoozedNotification\ScheduledNotification;
use App\Jobs\Google\CalendarEvent\CreateCalendarEventForAppointment;
use App\Jobs\Google\CalendarEvent\UpdateCalendarEvent;
use App\Models\GoogleMeeting;
use App\Notifications\AnonymousNotifiable;
use App\Notifications\AppointmentNotification;
use App\Notifications\AppointmentNotification2;
use Carbon\Carbon;

trait GoogleCalendar
{
    /**
     * @param Appointment $appointment
     */
    public function createCalendarEventForAppointment(Appointment $appointment)
    {
        return \Bus::dispatchNow(new CreateCalendarEventForAppointment($appointment, true));
    }

    /**
     * @param GoogleMeeting $googleMeet
     * @return mixed
     */
    public function updateCalendarEvent(GoogleMeeting $googleMeet)
    {
        return \Bus::dispatchNow(new UpdateCalendarEvent($googleMeet));
    }

    /**
     * @param GoogleMeeting $googleMeeting
     * @param $viaEmail
     * @param $viaSms
     * @param null $phone
     * @param null $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(
        GoogleMeeting $googleMeeting,
        Carbon $date,
        $viaEmail,
        $viaSecondaryEmail,
        $viaSms,
        $phone = null,
        $email = null,
        $secondaryEmail = null
    ) {
        $target = (new AnonymousNotifiable);

        if (!empty($email) && $viaEmail) {
            $target->route('mail', $email);
        }
      
        if (!empty($phone) && $viaSms) {
            $target->route('ringcentral_sms', $phone);
        }

        if (!empty($target->routes)) {
            $notification = ScheduledNotification::create(
                $target,
                new AppointmentNotification($googleMeeting),
                $date->startOfMinute()
            );

            if ($notification->getSendAt()->lte(Carbon::now())) {
                $notification->sendNow();
            }
        }

        if (!empty($phone) && $viaSms && $googleMeeting->allow_to_join_by_phone) {
            $target2 = (new AnonymousNotifiable);
            $target2->route('ringcentral_sms',  $phone);
            $notification2 = ScheduledNotification::create(
                $target2,
                new AppointmentNotification2($googleMeeting),
                $date->startOfMinute()
            );
            if ($notification2->getSendAt()->lte(Carbon::now())) {
                $notification2->sendNow();
            }
        }

        if (!empty($secondaryEmail) && $viaSecondaryEmail) {
            $target3 = (new AnonymousNotifiable);
            $target3->route('mail',  $secondaryEmail);
            $notification3 = ScheduledNotification::create(
                $target3,
                new AppointmentNotification($googleMeeting),
                $date->startOfMinute()
            );
            if ($notification3->getSendAt()->lte(Carbon::now())) {
                $notification3->sendNow();
            }
        }
    }
}
