<?php

namespace App\Console\Commands\SingleUse\OfficeAlly;

use Illuminate\Console\Command;
use App\Appointment;
use App\Helpers\Sites\OfficeAlly\Enums\AppointmentStatuses;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Status;
use App\Option;
use App\DTO\OfficeAlly\AppointmentResource;
use Carbon\Carbon;

class CreateAppointmentsForPeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oa:create-appointments-for-period {start-date} {end-date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $account = Option::OA_ACCOUNT_1;
        $officeAlly = new OfficeAllyHelper($account);

        Appointment::whereNull('idAppointments')
            ->whereDate('created_at', '>=', $this->argument('start-date'))
            ->whereDate('created_at', '<=', $this->argument('end-date'))
            ->each(function ($appointment) use (&$officeAlly) {
                $createData = new \App\DTO\OfficeAlly\Appointment([
                    'date' => Carbon::createFromTimestamp($appointment->time),
                    'officeId' => (int)$appointment->office->external_id,
                    'patientId' => (int)$appointment->patient->patient_id,
                    'reasonForVisit' => $appointment->reason_for_visit,
                    'providerId' => (int)$appointment->provider->officeally_id,
                    'visitLength' => $appointment->visit_length,
                    'resource' => new AppointmentResource([
                        'type' => optional($appointment->officeRoom)->external_id  ? AppointmentResource::TYPE_ROOM : 0,
                        'id' => optional($appointment->officeRoom)->external_id  ? (int)$appointment->officeRoom->external_id : 0,
                    ]),
                    'statusId' => AppointmentStatuses::ACTIVE,
                    'notes' => $appointment->notes ?? null,
                ]);
        
                $appointmentId = $officeAlly->createAppointment($createData);
                if (!$appointmentId) {
                    return;
                }
        
                $appointment->idAppointments = (int)$appointmentId;
                $appointment->save();
        
                $activeStatusId = Status::getActiveId();
                if ($appointment->appointment_statuses_id == $activeStatusId) {
                    return;
                }
        
                $statusName = Status::find($appointment->appointment_statuses_id)->external_id;
                $updateData = new \App\DTO\OfficeAlly\Appointment([
                    'id' => (int)$appointmentId,
                    'date' => Carbon::createFromTimestamp($appointment->time),
                    'officeId' => (int)$appointment->office->external_id,
                    'patientId' => (int)$appointment->patient->patient_id,
                    'reasonForVisit' => $appointment->reason_for_visit,
                    'providerId' => (int)$appointment->provider->officeally_id,
                    'visitLength' => $appointment->visit_length,
                    'resource' => new AppointmentResource([
                        'type' => optional($appointment->officeRoom)->external_id  ? AppointmentResource::TYPE_ROOM : 0,
                        'id' => optional($appointment->officeRoom)->external_id  ? (int)$appointment->officeRoom->external_id : 0,
                    ]),
                    'statusId' => (int)$statusName,
                    'notes' => $appointment->notes ?? null,
                ]);
        
                $officeAlly->editAppointment($updateData);
            });
    }
}
