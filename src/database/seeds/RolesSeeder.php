<?php

use App\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::firstOrCreate([
            'role' => 'admin',
        ]);
        
        Role::firstOrCreate([
            'role' => 'secretary',
        ]);

        Role::firstOrCreate([
            'role' => 'patient_relation_manager',
        ]);

        Role::firstOrCreate([
            'role' => 'provider',
        ]);

        \Illuminate\Support\Facades\Artisan::call('provider-role:add');
    }
}
