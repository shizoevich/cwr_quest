<?php

use App\Helpers\Sites\OfficeAlly\Enums\AppointmentStatuses;
use App\Status;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SyncExternalIdInAppointmentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            [
                'status' => 'Visit Created',
                'external_id' => AppointmentStatuses::VISIT_CREATED,
            ],
            [
                'status' => 'Rescheduled',
                'external_id' => AppointmentStatuses::RESCHEDULED,
            ],
            [
                'status' => 'Cancelled by Patient',
                'external_id' => AppointmentStatuses::CANCELLED_BY_PATIENT,
            ],
            [
                'status' => 'Cancelled by Provider',
                'external_id' => AppointmentStatuses::CANCELLED_BY_PROVIDER,
            ],
            [
                'status' => 'Last Minute Cancel by Patient',
                'external_id' => AppointmentStatuses::LAST_MINUTE_CANCEL_BY_PATIENT,
            ],
            [
                'status' => 'Patient Did Not Come',
                'external_id' => AppointmentStatuses::PATIENT_DID_NOT_COME,
            ],
            [
                'status' => 'Completed',
                'external_id' => AppointmentStatuses::COMPLETED,
            ],
            [
                'status' => 'Active',
                'external_id' => AppointmentStatuses::ACTIVE,
            ],
            [
                'status' => 'Last Minute Reschedule',
                'external_id' => AppointmentStatuses::LAST_MINUTE_RESCHEDULE,
            ],
            [
                'status' => 'Cancelled by Office',
                'external_id' => AppointmentStatuses::CANCELLED_BY_OFFICE,
            ],
            [
                'status' => 'Checked In',
                'external_id' => AppointmentStatuses::CHECKED_IN,
            ],
            [
                'status' => 'Confirmed',
                'external_id' => AppointmentStatuses::CONFIRMED,
            ],
            [
                'status' => 'In Room',
                'external_id' => AppointmentStatuses::IN_ROOM,
            ],
            [
                'status' => 'Left Message',
                'external_id' => AppointmentStatuses::LEFT_MESSAGE,
            ],
            [
                'status' => 'Checked Out',
                'external_id' => AppointmentStatuses::CHECKED_OUT,
            ],
        ];
        
        foreach ($data as $item) {
            Status::query()
                ->where('status', $item['status'])
                ->update([
                    'external_id' => $item['external_id'],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
