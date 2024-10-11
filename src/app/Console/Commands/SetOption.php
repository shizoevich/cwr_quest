<?php

namespace App\Console\Commands;

use App\Option;
use Illuminate\Console\Command;

class SetOption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'options:set-option {key} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set value in options table';

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
        Option::setOptionValue($this->argument('key'), $this->argument('value'));
    }
}
