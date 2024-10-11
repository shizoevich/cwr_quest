<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaxStatusSeedeer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fax_statuses')->insert([
            ['name' => 'public'],
            ['name' => 'private']
        ]);
    }
}
