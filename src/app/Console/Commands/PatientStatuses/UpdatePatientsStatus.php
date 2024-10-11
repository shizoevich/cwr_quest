<?php

namespace App\Console\Commands\PatientStatuses;

use Illuminate\Console\Command;

class UpdatePatientsStatus extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:update-statuses {--sync}';

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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $jobs = [];
        $jobs[] = (new \App\Jobs\Patients\SetNewPatientStatus())->onQueue('parser');
        $jobs[] = (new \App\Jobs\Patients\SetActivePatientStatus())->onQueue('parser');
        $jobs[] = (new \App\Jobs\Patients\SetInactivePatientStatus())->onQueue('parser');
        $jobs[] = (new \App\Jobs\Patients\SetLostPatientStatus())->onQueue('parser');
        $jobs[] = (new \App\Jobs\Patients\SetArchivedPatientStatus())->onQueue('parser');
        foreach($jobs as $job) {
            if($this->option('sync')) {
                \Bus::dispatchNow($job);
            } else {
                dispatch($job);
            }
        }
    }
}
