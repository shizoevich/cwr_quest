<?php

namespace App\Observers;

use App\Appointment;
use App\AppointmentNotification;
use App\Contracts\Models\Patient;
use App\Events\Appointment\TodayAppointmentListUpdated;
use App\Events\AppointmentChanged;
use App\Helpers\HIPAALogger;
use App\Jobs\Availability\UpdateRemainingLength;
use App\Jobs\PatientNotes\ClearAppointmentId;
use App\Jobs\PatientNotes\RestoreAppointmentId;
use App\Jobs\Salary\AssignVisitsToAppointments;
use App\Jobs\Salary\CalculateOvertime;
use App\Mail\Patient\SatisfactionSurvey;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Models\Patient\Inquiry\PatientInquiryStage;
use App\Models\Provider\Salary;
use App\Models\Provider\SalaryTimesheetVisit;
use App\PatientVisit;
use App\Repositories\NewPatientsCRM\PatientInquiry\PatientInquiryRepositoryInterface;
use App\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        event(
            new AppointmentChanged(
                $appointment->providers_id,
                Carbon::createFromTimestamp($appointment->time)->toDateTimeString()
            )
        );

        if (Carbon::createFromTimestamp($appointment->time)->isToday()) {
            event(new TodayAppointmentListUpdated([$appointment->providers_id]));
        }

        dispatch(
            new UpdateRemainingLength(
                Carbon::createFromTimestamp($appointment->time)->toDateString(),
                $appointment->providers_id
            )
        );

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($appointment),
                'event' => 'create',
                'data' => $appointment->getLogData(),
                'message' => $appointment->getCreateLogMessage(),
            ]
        );
    }

    public function updated(Appointment $appointment): void
    {
        $canceledStatuses = Status::getOtherCancelStatusesId();
        $visitCreatedStatusId = Status::getVisitCreatedId();
        $getCompletedId = Status::getCompletedId();

        // ToDo: add strict comparison mode (add 3rd parameter true)?
        if (in_array($appointment->appointment_statuses_id, $canceledStatuses)) {
            $appointmentsCollection = collect();
            $appointmentsCollection->push($appointment->getKey());
            Bus::dispatchNow(new ClearAppointmentId($appointmentsCollection));
        } else {
            $appointmentsCollection = collect();
            $appointmentsCollection->push($appointment);
            Bus::dispatchNow(new RestoreAppointmentId($appointmentsCollection));
        }

        // ToDo: add strict comparison?
        if (
            $appointment->appointment_statuses_id != Status::getActiveId()
            && $appointment->isDirty('appointment_statuses_id')
        ) {
            AppointmentNotification::query()
                ->where('appointment_id', $appointment->id)
                ->update(['status' => AppointmentNotification::STATUS_CONFIRMED,]);
        }

        // ToDo: add strict comparison?
        if (
            $appointment->getOriginal('appointment_statuses_id') == $visitCreatedStatusId    
            && $appointment->isDirty(['appointment_statuses_id', 'providers_id', 'patients_id', 'time'])
        ) {
            PatientVisit::query()
                ->where('appointment_id', $appointment->getKey())
                ->each(function ($patientVisit) {
                    $patientVisit->update(['appointment_id' => null]);
                });


            // ToDo: replace with ternary operator?
            if ($appointment->getOriginal('time') < $appointment->time) {
                $startDate = Carbon::createFromTimestamp($appointment->getOriginal('time'));
            } else {
                $startDate = Carbon::createFromTimestamp($appointment->time);
            }

            dispatch(new AssignVisitsToAppointments($startDate));
        }

        if (
             $appointment->getOriginal('appointment_statuses_id') ==  $getCompletedId
            && $appointment->isDirty(['appointment_statuses_id', 'providers_id', 'patients_id', 'time'])
        ) {
            PatientVisit::query()
                ->where('appointment_id', $appointment->getKey())
                ->where('from_completed_appointment', true)
                ->each(function ($patientVisit) {
                    $patientVisit->update(['appointment_id' => null]);
                    Salary::where('visit_id',  $patientVisit->id)->delete();
                    SalaryTimesheetVisit::where('visit_id',  $patientVisit->id)->delete();
                    $patientVisit->delete();
                });

            // ToDo: replace with ternary operator?
            if ($appointment->getOriginal('time') < $appointment->time) {
                $startDate = Carbon::createFromTimestamp($appointment->getOriginal('time'));
            } else {
                $startDate = Carbon::createFromTimestamp($appointment->time);
            }

            dispatch(new AssignVisitsToAppointments($startDate));
        }

        if ($appointment->isDirty('is_initial')) {
            $newVal = (bool)$appointment->is_initial;
            $oldVal = (bool)$appointment->getOriginal('is_initial');
            if ($newVal !== $oldVal) {
                $appointment->visit()->withTrashed()->each(function ($patientVisit) {
                    $patientVisit->update(['needs_update_salary' => 1]);
                });
            }
        }

        if (
            $appointment->isDirty(
                [
                    'note_on_paper',
                    'initial_assessment_type',
                    'initial_assessment_id',
                    'initial_assessment_created_at'
                ]
            )
            && $appointment->visit()->withTrashed()->exists()
        ) {
            $appointment->visit()->withTrashed()->each(function ($patientVisit) {
                $patientVisit->update(['needs_update_salary' => 1]);
            });
        }

        if ($appointment->isDirty(
            [
                'time',
                'visit_copay',
                'visit_length',
                'reason_for_visit',
                'patients_id',
                'providers_id',
                'offices_id',
                'office_room_id',
                'appointment_statuses_id',
            ]
        )) {
            event(
                new AppointmentChanged(
                    $appointment->providers_id,
                    Carbon::createFromTimestamp($appointment->time)->toDateTimeString()
                )
            );
        }

        if (
            $appointment->isDirty(['time', 'appointment_statuses_id', 'providers_id', 'patients_id'])
            && (Carbon::createFromTimestamp($appointment->time)->isToday()
                || Carbon::createFromTimestamp($appointment->getOriginal('time'))->isToday()
            )
        ) {
            event(
                new TodayAppointmentListUpdated(
                    [
                        $appointment->providers_id,
                        $appointment->getOriginal('providers_id')
                    ]
                )
            );
            dispatch(
                new CalculateOvertime(
                    Carbon::createFromTimestamp($appointment->time)->startOfWeek(),
                    Carbon::createFromTimestamp($appointment->time)->endOfWeek(),
                    $appointment->providers_id
                )
            );
        }

        if ($appointment->isDirty(['time', 'providers_id'])) {
            dispatch(
                new UpdateRemainingLength(
                    Carbon::createFromTimestamp($appointment->time)->toDateString(),
                    $appointment->providers_id
                )
            );
            dispatch(
                new UpdateRemainingLength(
                    Carbon::createFromTimestamp($appointment->getOriginal('time'))->toDateString(),
                    $appointment->getOriginal('providers_id')
                )
            );
        }

        // @todo uncomment when logic will be approved
        // $patient = $appointment->patient()->first();
        // if ($appointment->isDirty('appointment_statuses_id') && $appointment->appointment_statuses_id == Status::getCompletedId() && $patient->email) {
        //     $this->sendSatisfactionSurveyEmail(
        //         $patient->email,
        //         $appointment->provider->getTherapistFullname(),
        //         $appointment->patient->getFullName(),
        //         $appointment->time,
        //         $appointment->id
        //     );
        // }

        $dirtyFields = $appointment->getDirtyWithOriginal();

        if (count($dirtyFields)) {
            HIPAALogger::logEvent(
                [
                    'collection' => class_basename($appointment),
                    'event' => 'update',
                    'data' => $appointment->getLogData(),
                    'dirty_fields' => $dirtyFields,
                    'message' => $appointment->getUpdateLogMessage($dirtyFields),
                ]
            );
        }
    }

    public function deleted(Appointment $appointment): void
    {
        AppointmentNotification::query()->where('appointment_id', $appointment->id)->delete();
        $appointmentsCollection = collect();
        $appointmentsCollection->push($appointment->getKey());

        Bus::dispatchNow(new ClearAppointmentId($appointmentsCollection));

        $this->changeInquiryStageToOnHoldIfNeeded($appointment);

        PatientVisit::query()
            ->where('appointment_id', $appointment->getKey())
            ->each(function ($patientVisit) {
                $patientVisit->update(['appointment_id' => null]);
            });

        event(
            new AppointmentChanged(
                $appointment->providers_id,
                Carbon::createFromTimestamp($appointment->time)->toDateTimeString()
            )
        );

        if (Carbon::createFromTimestamp($appointment->time)->isToday()) {
            event(new TodayAppointmentListUpdated([$appointment->providers_id]));
        }

        dispatch(
            new UpdateRemainingLength(
                Carbon::createFromTimestamp($appointment->time)->toDateString(),
                $appointment->providers_id
            )
        );

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($appointment),
                'event' => 'delete',
                'data' => $appointment->getLogData(),
                'message' => $appointment->getDeleteLogMessage(),
            ]
        );
    }

    public function restored(Appointment $appointment): void
    {
        $appointmentsCollection = collect();
        $appointmentsCollection->push($appointment);

        Bus::dispatchNow(new RestoreAppointmentId($appointmentsCollection));

        dispatch(new AssignVisitsToAppointments(Carbon::createFromTimestamp($appointment->time)->startOfDay()));

        if (Carbon::createFromTimestamp($appointment->time)->isToday()) {
            event(new TodayAppointmentListUpdated([$appointment->providers_id]));
        }

        dispatch(
            new UpdateRemainingLength(
                Carbon::createFromTimestamp($appointment->time)->toDateString(),
                $appointment->providers_id
            )
        );

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($appointment),
                'event' => 'restore',
                'data' => $appointment->getLogData(),
                'message' => $appointment->getRestoreLogMessage(),
            ]
        );
    }

    private function changeInquiryStageToOnHoldIfNeeded($appointment): void
    {
        $inquiry = $appointment->patient->activeInquiry()->exists() ? $appointment->patient->activeInquiry()->first() : null;
        $needToSetInquiryStageOnHold = $inquiry && $inquiry->stage_id !== PatientInquiryStage::getOnHoldId() && !$inquiry->getFirstCompletedAppointment();

        if ($needToSetInquiryStageOnHold) {
            app()->make(PatientInquiryRepositoryInterface::class)->changeStage(
                $inquiry,
                [
                    'stage_id' => PatientInquiryStage::getOnHoldId(),
                ],
                PatientInquiry::REASON_FOR_STAGE_CHANGE_CANCELED_APPOINTMENT
            );
        }
    }

    private function sendSatisfactionSurveyEmail(
        string $patientEmail,
        string $therapistName,
        string $patientName,
        int $appointmentDate,
        int $appointmentId
    ): void {
        \Mail::to('belyy_vv@groupbwt.com')->send(new SatisfactionSurvey($therapistName, $patientName, $appointmentDate, $appointmentId));
        // \Mail::to($patientEmail)->send(new SatisfactionSurvey($therapistName, $patientName, $appointmentDate, $appointmentId));
    }
}
