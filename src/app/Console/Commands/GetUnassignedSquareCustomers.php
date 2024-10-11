<?php

namespace App\Console\Commands;

use App\PatientSquareAccount;
use App\Repositories\Square\ApiRepositoryInterface;
use Illuminate\Console\Command;

class GetUnassignedSquareCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'square:get-unassigned';

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
        $accounts = PatientSquareAccount::select([
            'external_id',
        ])
            ->whereNull('patient_id')
            ->get();

        $data = [];
        $squareApi = app()->make(ApiRepositoryInterface::class);

        foreach ($accounts as $account) {
            $squareCustomer = $squareApi->getCustomer($account->external_id);
            if(!$squareCustomer) {
                continue;
            }
            $firstName = $squareCustomer->getGivenName();
            $lastName = $squareCustomer->getFamilyName();
            $data[] = [
                $account->external_id,
                $firstName . ' ' . $lastName,
            ];
        }

        $this->table(['External ID', 'Name',], $data);
    }
}
