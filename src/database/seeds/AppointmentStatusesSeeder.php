<?php

use App\Status;
use Illuminate\Database\Seeder;

class AppointmentStatusesSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Status::create([
            'id' => 1,
            'status' => 'Visit Created'
        ]);
    }
}
