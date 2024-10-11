<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReasonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('visit_reasons')->insert([
            [
                'slug' => 'telehealth',
                 'title' => 'Telehealth',
            ],
            [
                'slug' => 'in-office',
                 'title' => 'In office',
            ],
        ]);
    }
}
