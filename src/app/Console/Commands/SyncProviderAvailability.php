<?php

namespace App\Console\Commands;

use App\Http\Controllers\ProviderAvailabilityCalendarController;
use App\Provider;
use App\ProviderWorkHour;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SyncProviderAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:provider-availability';

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
        $providers = Provider::select('id')->get();
        $request = new Request();
        foreach($providers as $provider) {
            $appointments = ProviderAvailabilityCalendarController::getAppointments($request, $provider->id);
            foreach($appointments as $appointment) {
                $start = Carbon::createFromTimestamp($appointment['time']);
                $workHour = ProviderWorkHour::firstOrCreate([
                    'office_id' => $appointment['office']['id'],
                    'office_room_id' => $appointment['office_room']['id'],
                    'start_time' => $start->format('H:i:s'),
                    'day_of_week' => $start->format('N') - 1,
                    'length' => $appointment['visit_length'],
                    'provider_id' => $provider->id,
                ]);
            }
        }
    }
}
