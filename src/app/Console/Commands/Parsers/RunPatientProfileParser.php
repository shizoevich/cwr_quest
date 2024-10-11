<?php

namespace App\Console\Commands\Parsers;

use App\Jobs\Parsers\Guzzle\PatientProfilesParser;
use App\Patient;
use Illuminate\Console\Command;

class RunPatientProfileParser extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:patient-profile';

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
     */
    public function handle()
    {
        $IDs = Patient::select('patient_id')
            ->where('patient_id', '!=', 11111111)//Test Patient
            ->notArchived()
            ->pluck('patient_id')
            ->toArray();
        dispatch(with(new PatientProfilesParser($IDs))->onQueue('daily-parser'));
    }
}
