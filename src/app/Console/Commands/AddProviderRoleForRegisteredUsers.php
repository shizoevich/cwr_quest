<?php

namespace App\Console\Commands;

use App\Role;
use App\User;
use Illuminate\Console\Command;

class AddProviderRoleForRegisteredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provider-role:add';

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
        $providerRoleId = Role::getRoleId('provider');
        $users = User::withTrashed()->whereDoesntHave('roles', function($query) use ($providerRoleId) {
            $query->where('role_id', '=', $providerRoleId);
        })->whereNotNull('provider_id')->get();
        foreach($users as $user) {
            $user->roles()->attach($providerRoleId);
        }
    }
}
