<?php

namespace App\Console\Commands;

use App\Option;
use Illuminate\Console\Command;

class SetSickHoursPerYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'options:set-sick-hours {hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set sick_hours_per_year in options table';

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
        Option::setOptionValue('sick_hours_per_year', $this->argument('hours'));
    }
}
