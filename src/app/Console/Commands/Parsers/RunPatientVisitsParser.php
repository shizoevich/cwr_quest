<?php

namespace App\Console\Commands\Parsers;

use App\Option;
use Illuminate\Console\Command;

class RunPatientVisitsParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:patient-visits {--full-time} {--threads=} {--month=} {--date=} {--only-visits}';

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
     */
    public function handle()
    {
        $options = [
            'full-time' => $this->hasOption('full-time') && $this->option('full-time'),
            'only-visits' => $this->hasOption('only-visits') && $this->option('only-visits'),
            'month' => $this->hasOption('month') ? $this->option('month') : null,
            'date' => $this->hasOption('date') ? $this->option('date') : null,
        ];
        dispatch(with(new \App\Jobs\Parsers\Guzzle\PatientVisitsParser($options))->onQueue('daily-parser'));
    }
}
