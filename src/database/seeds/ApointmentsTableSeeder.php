<?php

use Illuminate\Database\Seeder;

class ApointmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Appointment::class,5)->create();
    }
}
