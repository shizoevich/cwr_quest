<?php

namespace App\Console\Commands\SingleUse;

use App\Jobs\Parsers\Guzzle\PatientProfilesParser;
use App\Patient;
use App\PatientStatus;
use Illuminate\Console\Command;

class TmpRunPatientProfileParser extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:patient:profile';

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
        $statuses = [
            PatientStatus::getActiveId(),
            PatientStatus::getNewId()
        ];
        $ids = Patient::query()
            ->where('patient_id', '!=', 11111111)//Test Patient
            ->whereIn('status_id', $statuses)
            ->pluck('patient_id')
            ->toArray();
        dispatch(with(new PatientProfilesParser($ids, 15))->onQueue('daily-parser'));
    }
}
