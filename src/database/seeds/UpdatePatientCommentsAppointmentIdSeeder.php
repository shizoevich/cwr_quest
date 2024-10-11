<?php

use App\Appointment;
use App\PatientComment;
use App\Status;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePatientCommentsAppointmentIdSeeder extends Seeder
{
    const APPOINTMENT_COLLECTION_ID = 2;

    const APPOINTMENT_UPDATE_EVENT_ID = 9;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cancelStatusesIds = Status::getNewCancelStatusesId();
        $rescheduleStatusesIds = Status::getRescheduleStatusesId();

        PatientComment::query()
            ->whereNull('appointment_id')
            ->where('comment_type', '!=', PatientComment::DEFAULT_COMMENT_TYPE)
            ->orderBy('created_at', 'desc')
            ->chunk(1000, function ($comments) use ($cancelStatusesIds, $rescheduleStatusesIds) {
                foreach ($comments as $comment) {
                    dump('COMMENT: ' . $comment->id . ' - ' . $comment->comment_type);

                    if ($comment->comment_type === PatientComment::CANCELLATION_COMMENT_TYPE) {
                        $recordInLogger = DB::connection('mysql_logger')
                            ->table('hipaa_log_item')
                            ->where('collection_name_id', self::APPOINTMENT_COLLECTION_ID)
                            ->where('event_name_id', self::APPOINTMENT_UPDATE_EVENT_ID)
                            ->where('appeared_at', $comment->created_at)
                            ->where(function ($query) use ($cancelStatusesIds) {
                                foreach ($cancelStatusesIds as $statusId) {
                                    $query->orWhere('message', 'like', "%New status id changed from % to '$statusId';%");
                                }
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if (isset($recordInLogger)) {
                            $recordData = json_decode($recordInLogger->data);
                            $comment->update(['appointment_id' => $recordData->id]);
                        }
                    }

                    if ($comment->comment_type === PatientComment::RESCHEDULE_COMMENT_TYPE) {
                        $recordInLogger = DB::connection('mysql_logger')
                            ->table('hipaa_log_item')
                            ->where('collection_name_id', self::APPOINTMENT_COLLECTION_ID)
                            ->where('event_name_id', self::APPOINTMENT_UPDATE_EVENT_ID)
                            ->where('appeared_at', $comment->created_at)
                            ->where(function ($query) use ($rescheduleStatusesIds) {
                                foreach ($rescheduleStatusesIds as $statusId) {
                                    $query->orWhere('message', 'like', "%New status id changed from % to '$statusId';%");
                                }
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if (isset($recordInLogger)) {
                            $recordData = json_decode($recordInLogger->data);
                            $comment->update(['appointment_id' => $recordData->id]);
                        }
                    }

                    if ($comment->comment_type === PatientComment::CREATION_COMMENT_TYPE) {
                        $fiveMinutesBeforeCommentCreatedAt = Carbon::parse($comment->created_at)->subMinutes(5);
                        $fiveMinutesAfterCommentCreatedAt = Carbon::parse($comment->created_at)->addMinutes(5);

                        $appointment = Appointment::query()
                            ->where('patients_id', $comment->patient_id)
                            ->whereBetween('created_at', [$fiveMinutesBeforeCommentCreatedAt, $fiveMinutesAfterCommentCreatedAt])
                            ->first();

                        if (isset($appointment)) {
                            $comment->update(['appointment_id' => $appointment->id]);
                        }
                    }
                }
            });
    }
}
