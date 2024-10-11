<?php

use App\Models\AppointmentRescheduleSubStatus;
use Illuminate\Database\Seeder;

class AppointmentRescheduleSubStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rescheduleSubStatuses = [
            "Rescheduled by Office",
            "Rescheduled by Patient",
            "Rescheduled by Provider",
        ];

        foreach ($rescheduleSubStatuses as $statusName) {
            AppointmentRescheduleSubStatus::updateOrCreate(
                ['status' => $statusName],
                ['status' => $statusName],
            );
        }
    }
}
