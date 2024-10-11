<?php

namespace App\Traits\Appointments;

use App\Appointment;
use App\AppointmentNotification;
use App\Jobs\Notifications\RingcentralSms;
use App\Mail\Patient\FirstAppointment;
use App\Office;
use App\Patient;
use App\PatientStatus;
use App\Provider;
use Carbon\Carbon;

trait SendProviderNotification
{
    /**
     * @param Appointment $appointment
     * @param Provider    $provider
     * @param Patient     $patient
     * @param Office      $office
     */
    public function sendFirstAppointmentNotifications(Appointment $appointment, Provider $provider, Patient $patient, Office $office)
    {
        $appointmentTime = Carbon::createFromTimestamp($appointment->time);
        $formattedDate = $appointmentTime->format('m/d/Y \a\t h:i A');
        $office = str_replace('New ', '', $office->office);
        $message = "You got a new patient referral from CWR! The new appt is scheduled on {$formattedDate} in {$office}. Login to EHR to view more details.";

        try {
            \Mail::to($provider->user->email)->send(new FirstAppointment($formattedDate, $office));
        } catch (\Throwable $exception) {
            \Log::error($exception);
        }

        if (isset($provider->phone)) {
            \Bus::dispatchNow(new RingcentralSms($provider->phone, $message));
        }

        $log = \Carbon\Carbon::now()->toDateTimeString() . " Phone: {$provider->phone} | Patient OfficeAlly ID: {$patient->patient_id} -> $message\n";
        file_put_contents(storage_path('logs/sent-sms.log'), $log, FILE_APPEND);

        $appointment->appointmentNotification()->create([
            'provider_id' => $provider->getKey(),
            'type' => AppointmentNotification::TYPE_NEW_PATIENT,
            'status' => AppointmentNotification::STATUS_NEW
        ]);
    }

    /**
     * @param $patient
     * @param $appointmentTime
     */
    public function changeDischargedToActive(Patient $patient, $appointmentTime)
    {
        $dates = [
            $patient->assessmentForms()->discharge()->max('created_at'),
            $patient->documents()->discharged()->max('created_at'),
            $patient->electronicDocuments()->discharge()->max('created_at')
        ];

        foreach ($dates as &$date) {
            if ($date) {
                $date = Carbon::parse($date)->startOfDay()->timestamp;
            }
        }
        sort($dates);
        $maxDate = last($dates);
        if ($maxDate && $appointmentTime->gt(Carbon::createFromTimestamp($maxDate)->endOfDay())) {
            PatientStatus::changeStatusAutomatically($patient->id, 'discharged_to_active');
        }
    }
}