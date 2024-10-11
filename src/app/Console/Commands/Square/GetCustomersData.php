<?php

namespace App\Console\Commands\Square;

use App\PatientSquareAccount;
use App\Repositories\Square\ApiRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class GetCustomersData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'square:get-customers-data {--force} {--only-unattached} {--force-unattached}';

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
        if ($this->option('only-unattached') && $this->option('force')) {
            $this->error('--force with --only-unattached is not allowed.');

            return;
        }
        if ($this->option('only-unattached') && $this->option('force-unattached')) {
            $this->error('--force-unattached with --only-unattached is not allowed.');

            return;
        }
        $customers = PatientSquareAccount::query()
            ->when($this->option('only-unattached'), function (Builder $query) {
                $query->whereNull('patient_id');
                $query->whereNull('first_name');
                $query->whereNull('last_name');
                $query->whereNull('email');
            })
            ->when($this->option('force-unattached'), function (Builder $query) {
                $query->whereNull('patient_id');
            })
            ->when(!$this->option('force'), function (Builder $query) {
                $query->whereNull('first_name');
                $query->whereNull('last_name');
                $query->whereNull('email');
            })
            ->distinct()
            ->get();
        $job = (new \App\Jobs\Square\GetCustomersData($customers))->onQueue('payments');
        dispatch($job);
    }
}
