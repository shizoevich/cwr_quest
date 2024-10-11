<?php

namespace App\Console\Commands\SingleUse;

use App\Provider;
use App\Role;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateInsuranceUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:insurance:create {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $email = $this->argument('email');
        $password = $this->argument('password');
        $role = Role::firstOrCreate(['role' => 'insurance_audit']);
        $provider = Provider::query()->updateOrCreate([
            'provider_name' => 'Kaiser Permanente',
            'tridiuum_sync_availability' => false,
            'tridiuum_sync_appointments' => false,
        ]);
        $user = User::query()->create([
            'email' => $email,
            'password' => bcrypt($password),
            'profile_completed_at' => Carbon::now(),
            'provider_id' => $provider->getKey(),
        ]);
        $user->meta()->update([
            'firstname' => 'Kaiser',
            'lastname' => 'Permanente',
        ]);
        $user->roles()->attach($role->getKey());
    }
}
