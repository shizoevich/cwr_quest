<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use App\Appointment;
use App\Status;
use Carbon\Carbon;

class SetVisitCreatedStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:set-visit-created {--start-date=} {--end-date=}';

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
        $startDate = null;
        $endDate = null;
        if ($this->option('start-date') && $this->option('end-date')) {
            $startDate = Carbon::parse($this->option('start-date'));
            $endDate = Carbon::parse($this->option('end-date'));
        } else if ($this->option('start-date') || $this->option('end-date')) {
            $temp = $this->option('start-date') ?? $this->option('end-date');
            $startDate = Carbon::parse($temp)->startOfMonth();
            $endDate = Carbon::parse($temp)->endOfMonth();
        } else {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::today()->endOfMonth();
        }

        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();
        $appointmentPayload = [
            'is_creating_visit_inprogress' => false,
            'appointment_statuses_id' => $visitCreatedId,
            'new_status_id' => $visitCreatedId,
            'is_warning' => false,
            'error_message' => null,
        ];

        Appointment::query()
            ->select(['appointments.*', \DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->where('appointment_statuses_id', $completedId)
            ->where('error_message', 'LIKE', '%Visit with DOS%')
            ->havingRaw("appt_date >= DATE('{$startDate->toDateString()}')")
            ->havingRaw("appt_date <= DATE('{$endDate->toDateString()}')")
            ->each(function ($appointment) use (&$appointmentPayload) {
                $appointment->update($appointmentPayload);
            });
    }
}
