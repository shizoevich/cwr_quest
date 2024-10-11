<?php

use App\Status;
use Illuminate\Database\Seeder;

class StatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['id' => 1, 'external_id' => 326, 'status' => 'Visit Created'],
            ['id' => 2, 'external_id' => 327, 'status' => 'Rescheduled'],
            ['id' => 3, 'external_id' => 321, 'status' => 'Cancelled by Patient'],
            ['id' => 4, 'external_id' => 324, 'status' => 'Cancelled by Provider'],
            ['id' => 5, 'external_id' => 329, 'status' => 'Last Minute Cancel by Patient'],
            ['id' => 6, 'external_id' => 323, 'status' => 'Patient Did Not Come'],
            ['id' => 7, 'external_id' => 325, 'status' => 'Completed'],
            ['id' => 8, 'external_id' => 320, 'status' => 'Active'],
            ['id' => 9, 'external_id' => 330, 'status' => 'Last Minute Reschedule'],
            ['id' => 10, 'external_id' => 322, 'status' => 'Cancelled by Office'],
            ['id' => 11, 'external_id' => 332, 'status' => 'Checked In'],
            ['id' => 12, 'external_id' => 328, 'status' => 'Confirmed'],
            ['id' => 13, 'external_id' => 331, 'status' => 'In Room'],
            ['id' => 14, 'external_id' => 361, 'status' => 'Left Message'],
            ['id' => 15, 'external_id' => 333, 'status' => 'Checked Out'],
        ];

        foreach($statuses as $status) {
            Status::create($status);
        }
    }
}
