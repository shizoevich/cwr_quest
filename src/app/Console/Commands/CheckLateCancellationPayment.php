<?php

namespace App\Console\Commands;

use App\Patient;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class CheckLateCancellationPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient:check-late-cancellation-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сhecks whether it is possible to take money from the patient for late cancellation';

    protected function configure()
    {
        $this->addArgument('patientId', InputArgument::REQUIRED, 'The patient id to check for late cancellation payment.');
    }
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
        $patient = Patient::find($this->argument('patientId'));

        dd($patient->canChargeLateCancellationFee());
    }
}
