<?php

namespace App\Console\Commands\Tridiuum;

use App\Option;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChangeTridiuumPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tridiuum:change-password {login} {password}';

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
        $login = $this->argument('login');
        $newPassword = $this->argument('password');
        $credentials = Option::getOptionValue('tridiuum_credentials');
        $credentials = json_decode($credentials, true);
        $credentials[$login]['login'] = $login;
        $credentials[$login]['password'] = encrypt($newPassword);
        $credentials[$login]['updated_at'] = Carbon::now()->toDateTimeString();
        Option::setOptionValue('tridiuum_credentials', json_encode($credentials));
    }
}
