<?php

namespace App\Console\Commands\Salary;

use App\Appointment;
use App\Provider;
use Illuminate\Console\Command;
use App\User;
use Carbon\Carbon;
class SoftDeleteProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:soft-delete';

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
        $startDateUnix = strtotime('-1 month');
        $endDateUnix = time(); 
        
        $providersId = User::onlyTrashed()
            ->where('deleted_at', '>=', date('Y-m-d H:i:s', $startDateUnix))
            ->pluck('provider_id');
        
        foreach ($providersId as $providerId) {
            $appointmentsCount = Appointment::where('providers_id', $providerId)
                ->where('time', '>=', $startDateUnix)
                ->where('time', '<=', $endDateUnix)
                ->whereIn('appointment_statuses_id', ['7', '1'])
                ->count();
        
            if ($appointmentsCount == 0) {
                Provider::where('id', $providerId)->update(['deleted_at' => Carbon::now()]);
            }
        }  
        
    }
}
