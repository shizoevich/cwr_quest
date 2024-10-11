<?php

namespace App\Console\Commands\Parsers;

use App\Jobs\Square\GetCatalogItems;
use Illuminate\Console\Command;

class RunSquareCatalogItemsParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:square-catalog-items';

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
        \Bus::dispatchNow(new GetCatalogItems());
    }
}
