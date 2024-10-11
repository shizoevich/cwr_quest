<?php

namespace App\Jobs\Officeally;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Exceptions\Officeally\OfficeallyException;
use App\Jobs\Parsers\Guzzle\PatientVisitsParser;
use App\Option;
use App\Status;
use App\Appointment;
use App\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use App\Helpers\RetryJobQueueHelper;
use Carbon\Carbon;

class CreateVisits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private $appointments;

    /**
     * Create a new job instance.
     *
     * @param array $appointments
     */
    public function __construct(array $appointments)
    {
        $this->appointments = $appointments;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();
        $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_3);
        $dates = [];
        $appointmentIds = array_pluck($this->appointments, 'id');
        $appointmentRulesMapping = array_pluck($this->appointments, null, 'id');
        $supervisorIds = array_values(array_filter(array_pluck($this->appointments, 'supervisor_id'), function ($value) {
            return isset($value);
        }));
        $supervisorsMapping = Provider::query()
            ->whereIn('id', $supervisorIds)
            ->get()
            ->reduce(function ($carry, $item) {
                $carry[$item->id] = $item;
                return $carry;
            }, []);

        Appointment::query()
            ->whereIn('id', $appointmentIds)
            ->where('appointment_statuses_id', $completedId)
            ->with('patient')
            ->chunkById(100, function (Collection $appointments) use (&$dates, $completedId, $visitCreatedId, $officeAllyHelper, &$appointmentRulesMapping, &$supervisorsMapping) {
                Appointment::query()
                    ->whereIn('id', $appointments->pluck('id'))
                    ->update([
                        'is_creating_visit_inprogress' => true,
                        'start_creating_visit' => Carbon::now(),
                    ]);
                
                $appointments->each(function (Appointment $appointment) use (&$dates, $completedId, $visitCreatedId, $officeAllyHelper, &$appointmentRulesMapping, &$supervisorsMapping) {
                    $appointmentDate = Carbon::createFromTimestamp($appointment->time);
                    if (!in_array($appointmentDate->toDateString(), $dates)) {
                        $dates[] = $appointmentDate->toDateString();
                    }
                    $appointmentPayload = [
                        'is_creating_visit_inprogress' => false,
                        'appointment_statuses_id' => $visitCreatedId,
                        'new_status_id' => $visitCreatedId,
                        'is_warning' => false,
                        'error_message' => null,
                    ];

                    $supervisor = null;
                    $supervisorId = $appointmentRulesMapping[$appointment->getKey()]['supervisor_id'];
                    if (isset($supervisorId)) {
                        $supervisor = $supervisorsMapping[$supervisorId];
                    }

                    try {
                        if (!$appointment->idAppointments || RetryJobQueueHelper::checkAppointmentJobs($appointment->id)) {
                            $appointmentPayload['error_message'] = 'OfficeAlly error, please try again later.';
                            $appointmentPayload['appointment_statuses_id'] = $completedId;
                            $appointmentPayload['new_status_id'] = $completedId;
                            $appointmentPayload['is_warning'] = true;
                        } else {
                            $officeAllyHelper->createVisit(
                                $appointment,
                                false,
                                $appointmentRulesMapping[$appointment->getKey()]['accept_change_cpt'],
                                $appointmentRulesMapping[$appointment->getKey()]['accept_change_pos'],
                                $appointmentRulesMapping[$appointment->getKey()]['accept_change_modifier_a'],
                                $supervisor
                            );
                        }
                    } catch (OfficeallyAuthenticationException $e) {
                        $appointmentPayload['error_message'] = 'OfficeAlly error, please try again later.';
                        $appointmentPayload['appointment_statuses_id'] = $completedId;
                        $appointmentPayload['new_status_id'] = $completedId;
                        $appointmentPayload['is_warning'] = true;
                        \App\Helpers\SentryLogger::officeAllyCaptureException($e);
                    } catch (OfficeallyException $e) {
                        $appointmentPayload['error_message'] = $e->getHumanReadableMessage();
                        $appointmentPayload['appointment_statuses_id'] = $completedId;
                        $appointmentPayload['new_status_id'] = $completedId;
                        \App\Helpers\SentryLogger::officeAllyCaptureException($e);
                    } catch (\Exception $e) {
                        \Log::error($e->getTraceAsString());
                        $appointmentPayload['appointment_statuses_id'] = $completedId;
                        $appointmentPayload['new_status_id'] = $completedId;
                        $appointmentPayload['error_message'] = 'Manual intervention required.';
                        \App\Helpers\SentryLogger::officeAllyCaptureException($e);
                    }

                    $appointment->update($appointmentPayload);
                });
            });
        
        foreach ($dates as $date) {
            \Bus::dispatchNow(new PatientVisitsParser([
                'full-time' => false,
                'only-visits' => true,
                'month' => null,
                'date' => $date,
            ], false, false));
        }
    }
}
