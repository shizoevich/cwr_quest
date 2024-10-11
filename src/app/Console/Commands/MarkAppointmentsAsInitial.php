<?php

namespace App\Console\Commands;

use App\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class MarkAppointmentsAsInitial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:mark-as-initial';

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
            ->select([
                'patients_id',
                \DB::raw('MIN(`time`) AS min_time')
            ])
            ->onlyVisitCreated()
            ->groupBy('patients_id')
            ->each(function (Appointment $appointment) {
                Appointment::query()
                    ->where([
                        'time'        => $appointment->min_time,
                        'patients_id' => $appointment->patients_id,
                    ])
                    ->each(function ($appointmentFromGroup) {
                        $appointmentFromGroup->update(['is_initial' => true]);
                    });
            }, 500);
    }
}
