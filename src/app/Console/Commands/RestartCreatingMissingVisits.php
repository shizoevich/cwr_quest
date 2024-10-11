<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Models\Provider\ProviderSupervisor;
use App\Jobs\Officeally\CreateVisits;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class RestartCreatingMissingVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missing-visits:create';

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
            ->where('is_creating_visit_inprogress', 1)
            ->where(function($query) {
                $query->whereNull('start_creating_visit')
                    ->orWhere('start_creating_visit', '<=', Carbon::now()->subMinutes(30));
            })
            ->chunkById(100, function(Collection $appointments) {
                Appointment::query()
                    ->whereIn('id', $appointments->pluck('id'))
                    ->each(function ($appointmentToUpdate) {
                        $appointmentToUpdate->update([
                            'start_creating_visit' => Carbon::now(),
                        ]);
                    });

                $mappedAppointments = $appointments->map(function(Appointment $appointment) {
                    $providerSupervisor = ProviderSupervisor::getSupervisorForDate($appointment->providers_id, Carbon::createFromTimestamp($appointment->time));

                    return [
                        'id' => $appointment->getKey(),
                        'accept_change_cpt' => true,
                        'accept_change_modifier_a' => true,
                        'accept_change_pos' => true,
                        'supervisor_id' => optional($providerSupervisor)->supervisor_id,
                    ];
                })->toArray();
                
                dispatch(with(new CreateVisits($mappedAppointments))->onQueue('officeally-billing'));
            });
    }
}
