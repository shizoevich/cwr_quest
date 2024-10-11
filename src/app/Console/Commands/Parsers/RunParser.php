<?php

namespace App\Console\Commands\Parsers;

use App\Jobs\Parsers\Guzzle\AppointmentsParser;
use App\Jobs\Parsers\Guzzle\BillingProvidersParser;
use App\Jobs\Parsers\Guzzle\OfficeRoomsParser;
use App\Jobs\Parsers\Guzzle\PatientsParser;
use App\Jobs\Parsers\Guzzle\PatientVisitsParser;
use App\Jobs\Parsers\Guzzle\ProvidersParser;
use App\Jobs\UpdateProviderInsurances;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class RunParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:run {--parser=} {--upcoming-days=} {--prev-days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run phantom js parser to take full html pages, then run dom crawler to take appointments data from pages and save it to DB';

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
     * @throws \Exception
     */
    public function handle()
    {
        $parser = $this->option('parser');
        $hasUpcomingDays = $this->hasOption('upcoming-days') && $this->option('upcoming-days');
        $status = \Queue::size('parser') > 0;
        if(!$status || ($hasUpcomingDays && $parser === 'appointments')) {
            $jobs = [];
            if($parser === 'patients') {
                $job = (new PatientsParser())->onQueue('parser');
                dispatch($job);
            } else if($parser === 'providers') {
                dispatch(with(new ProvidersParser())->onQueue('parser'));
            } else if($parser === 'appointments') {
                $upcomingDays = null;
                $prevDays = null;
                if($this->hasOption('upcoming-days') && !is_null($this->option('upcoming-days'))) {
                    $upcomingDays = intval($this->option('upcoming-days'));
                }
                if($this->hasOption('prev-days') && !is_null($this->option('prev-days'))) {
                    $prevDays = intval($this->option('prev-days'));
                }
                $jobs[] = (new AppointmentsParser($prevDays, $upcomingDays))->onQueue('parser');
                $jobs[] = (new UpdateProviderInsurances())->onQueue('parser');
                foreach($jobs as $job) {
                    dispatch($job);
                }
                Artisan::call('patients:update-statuses');
            } else {
                $jobs[] = (new OfficeRoomsParser())->onQueue('parser');
                $jobs[] = (new BillingProvidersParser())->onQueue('parser');
                $jobs[] = (new ProvidersParser())->onQueue('parser');
                $jobs[] = (new PatientsParser())->onQueue('parser');
                $jobs[] = (new AppointmentsParser())->onQueue('parser');
                $jobs[] = (new UpdateProviderInsurances())->onQueue('parser');
                $jobs[] = (new PatientVisitsParser(['only-visits' => true]))->onQueue('parser');
                foreach($jobs as $job) {
                    dispatch($job);
                }
                Artisan::call('patients:update-statuses');
            }
        } else {
            $this->warn('Woops! Parser is already running.');
        }

    }
}
