<?php

namespace App\Traits\Patient;

use App\Appointment;
use App\Jobs\Officeally\Retry\RetryDeleteUpcomingAppointments;
use App\Option;
use App\Patient;
use App\PatientComment;
use App\PatientStatus;
use App\Provider;
use Carbon\Carbon;

trait PatientDischargeTrait
{
    protected function discharge(Patient $patient, ?Provider $provider = null)
    {
        if ($provider) {
            $systemComments = [];

            $patient->appointments()
                ->where('time', '>=', Carbon::today()->addDay()->startOfDay()->timestamp)
                ->where('providers_id', $provider->id)
                ->each(function (Appointment $appointment) use (&$systemComments) {
                    $time = Carbon::createFromTimestamp($appointment->time);
                    $comment = trans('comments.appointment_deleted_from_office_ally', [
                        'apptdate' => $time->format('m/d/Y'),
                        'appttime' => $time->format('h:iA'),
                    ]);
                    $systemComments[] = [
                        'comment' => $comment,
                        'patient_id' => $appointment->patients_id,
                    ];
                    $appointment->delete();
                });
            
            PatientComment::bulkAddComments($systemComments, true);
            dispatch(new RetryDeleteUpcomingAppointments($patient->id, Option::OA_ACCOUNT_3, $provider->officeally_id));
        }

        PatientStatus::changeStatusAutomatically($patient->id, 'to_discharged');
    }
}
