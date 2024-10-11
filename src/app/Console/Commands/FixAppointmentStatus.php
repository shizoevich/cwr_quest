<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Status;
use Carbon\Carbon;
use Illuminate\Console\Command;
use MailchimpTransactional\ApiClient;

class FixAppointmentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointment-status:fix';

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
        Appointment::query()
            ->whereNotNull('start_completing_date')
            ->where('appointment_statuses_id', '!=', \DB::raw('new_status_id'))
            ->whereNotNull('new_status_id')
            ->where('appointment_statuses_id', Status::getActiveId())
            ->where('start_completing_date', '<=', Carbon::now()->subHour())
            ->each(function ($appointment) {
                $appointment->update(['start_completing_date' => null]);
            });
    }
}
