<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\VideoSession\Show;
use App\Http\Requests\Patient\VideoSession\Store;
use App\Http\Requests\Patient\VideoSession\StoreUphealVideoSession;
use App\Appointment;
use App\Patient;
use App\Provider;
use App\Models\GoogleMeeting;
use App\Models\UphealMeeting;
use App\Notifications\AnonymousNotifiable;
use App\Notifications\AppointmentNotification;
use App\Notifications\AppointmentNotification2;
use App\Services\Twilio\TwilioLookup;
use App\Traits\Appointments\GoogleCalendar;
use App\Traits\Appointments\VideoSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Helpers\UphealHelper;
use Twilio\Exceptions\RestException;
use App\Exceptions\PhoneIsUnableToReceiveSmsException;
use App\Components\SnoozedNotification\ScheduledNotification;

class PatientVideoSessionController extends Controller
{
    use GoogleCalendar, VideoSession;

    /**
     * @param Store   $request
     * @param Patient $patient
     */
    public function store(Store $request, Patient $patient)
    {
        if ($request->has('phone') && $request->input('send_via_sms')) {
            try {
                TwilioLookup::validatePhone($request->input('phone'));
            } catch (RestException | PhoneIsUnableToReceiveSmsException $e) {
                // exception is handled in App\Exceptions\Handler
                throw new PhoneIsUnableToReceiveSmsException($request->input('phone'));
            }
        }

        $googleMeetings = [];
        $appointments = Appointment::query()->select(['id', 'time'])->whereKey(array_pluck($request->appointments, 'id'))->get();

        foreach ($request->appointments as $index => $appointment) {
            if(!$appointment['date']) {
                continue;
            }
            $appointmentModel = $appointments->where('id', $appointment['id'])->first();
            if(Carbon::parse($appointment['date'])->gt(Carbon::createFromTimestamp($appointmentModel->time))) {
                return response()->json([
                    "appointments.{$index}.date" => [
                        trans('validation.before_or_equal', [
                            'attribute' => "appointments.{$index}.date",
                            'date' => Carbon::createFromTimestamp($appointmentModel->time)->format('m/d/Y h:i A'),
                        ]),
                    ]
                ], 422);
            }
        }

        foreach ($request->appointments as $appointment) {
            /** @var GoogleMeeting $googleMeeting */
            $appointmentId = data_get($appointment, 'id');
            $googleMeeting = GoogleMeeting::query()
                ->where('patient_id', $patient->getKey())
                ->where('provider_id', auth()->user()->provider_id)
                ->where('appointment_id', $appointmentId)
                ->first();

            if (empty($googleMeeting)) {
                $appointmentModel = $patient->appointments()->where('id', $appointmentId)->first();
                $googleMeeting = $this->createCalendarEventForAppointment($appointmentModel);
            }
            
            $googleMeeting->allow_to_join_by_phone = $appointment['allow_to_join_by_phone'] ?? false;
            $googleMeeting->save();

            $target = (new AnonymousNotifiable);
            $date = $appointment['date'] ? Carbon::parse($appointment['date']) : Carbon::now();
            if (!empty($request->input('email')) && $request->input('send_via_email')) {
                $target->route('mail', $request->input('email'));
            }
            if (!empty($request->input('phone')) && $request->input('send_via_sms')) {
                $target->route('ringcentral_sms',  $request->input('phone'));
            }

            $notification = ScheduledNotification::create(
                $target,
                new AppointmentNotification($googleMeeting),
                $date->startOfMinute()
            );
            
            if($notification->getSendAt()->lte(Carbon::now())) {
                $notification->sendNow();
            }

            if(!empty($request->input('phone')) && $request->input('send_via_sms') && $googleMeeting->allow_to_join_by_phone) {
                $target2 = (new AnonymousNotifiable);
                $target2->route('ringcentral_sms',  $request->input('phone'));
                $notification2 = ScheduledNotification::create(
                    $target2,
                    new AppointmentNotification2($googleMeeting),
                    $date->startOfMinute()
                );
                if($notification2->getSendAt()->lte(Carbon::now())) {
                    $notification2->sendNow();
                }
            }

            if(!empty($request->input('secondary_email')) && $request->input('send_via_secondary_email')) {
                $target3 = (new AnonymousNotifiable);
                $target3->route('mail',  $request->input('secondary_email'));
                $notification3 = ScheduledNotification::create(
                    $target3,
                    new AppointmentNotification($googleMeeting),
                    $date->startOfMinute()
                );
                if($notification3->getSendAt()->lte(Carbon::now())) {
                    $notification3->sendNow();
                }
            }
            
            $googleMeetings[] = $googleMeeting;
        }

        return response()->json([
            'google_meetings' => $googleMeetings,
        ]);
    }

    /**
     * @param Show $request
     * @param GoogleMeeting $videoSession
     * @param Patient $patient
     * @return JsonResponse
     */
    public function show(Show $request, Patient $patient, GoogleMeeting $videoSession)
    {
        return response()->json($videoSession);
    }

    // @todo change logic when "upheal" integration will be finished
    public function storeUphealVideoSession(StoreUphealVideoSession $request, Patient $patient)
    {
        if (!empty($request->input('phone')) && $request->input('send_via_sms')) {
            try {
                TwilioLookup::validatePhone($request->input('phone'));
            } catch (RestException | PhoneIsUnableToReceiveSmsException $e) {
                return response()->json([
                    "phone" => [$e->getMessage()]
                ], 422);
            }
        }

        $appointment = $patient->appointments()->where('id', $request->appointment['id'])->first();
        $provider = Provider::withTrashed()->where('id', $request->input('provider_id'))->first();

        if ($request->appointment['date'] && Carbon::parse($request->appointment['date'])->gt(Carbon::createFromTimestamp($appointment->time))) {
            return response()->json([
                "appointment.date" => [
                    trans('validation.before_or_equal', [
                        'attribute' => "appointment.date",
                        'date' => Carbon::createFromTimestamp($appointment->time)->format('m/d/Y h:i A'),
                    ]),
                ]
            ], 422);
        }
        
        $this->setPatientEmailIfEmpty($patient, $request->input('email'));

        if (empty($patient->upheal_user_id)) {
            try {
                UphealHelper::createPatient($provider, $patient);
            } catch (\Exception $exception) {
                return response()->json(null, 409);
            }
        }

        $uphealMeeting = UphealMeeting::where('appointment_id', $appointment->id)->first();
        if (empty($uphealMeeting)) {
            $uphealMeeting = UphealMeeting::create([
                'patient_id' => $patient->id,
                'provider_id' => $provider->id,
                'appointment_id' => $appointment->id,
            ]);
        }

        $invitationDate = $request->appointment['date'] ? Carbon::parse($request->appointment['date']) : Carbon::now();

        UphealHelper::sendNotification(
            $uphealMeeting,
            $invitationDate,
            $request->input('send_via_email'),
            $request->input('send_via_secondary_email'),
            $request->input('send_via_sms'),
            $request->input('phone'),
            $request->input('email'),
            $request->input('secondary_email')
        );

        return response()->json([
            'upheal_meeting' => $uphealMeeting->load(['patient']),
        ]);
    }
}