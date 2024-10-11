<?php

namespace App\Console\Commands\Parsers;

use App\Jobs\Parsers\Guzzle\PatientAlertsParser;
use Illuminate\Console\Command;

class RunPatientAlertsParser extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:patient-alerts';

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
        dispatch(with(new PatientAlertsParser())->onQueue('daily-parser'));
    }
}
