<?php

namespace App\Jobs;

use App\Appointment;
use App\Helpers\ExceptionNotificator;
use App\Jobs\PatientNotes\ClearAppointmentId;
use App\Notifications\AnErrorOccurred;
use App\PatientComment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;

class DeleteAppointments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $parsedAt;
    private $prevDays;
    private $upcomingDays;

    const MAX_NOT_FOUND_COUNT = 3;
    const MAX_DELETING_APPOINTMENTS_COUNT = 15;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($parsedAt, $prevDays, $upcomingDays) {
        $this->parsedAt = $parsedAt;
        $this->prevDays = $prevDays;
        $this->upcomingDays = $upcomingDays;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $parsedAtTime = Carbon::createFromTimestamp($this->parsedAt);

        $startTime = clone $parsedAtTime;
        $endTime = clone $parsedAtTime;

        if ($this->prevDays > 0) {
            $startTime = $startTime->subDays($this->prevDays)
                ->endOfDay()
                ->timestamp;
        } else {
            $startTime = $startTime->subDays(config('parser.parsing_depth'))
                ->endOfDay()
                ->timestamp;
        }

        if ($this->upcomingDays > 0) {
            $endTime = $endTime->addDays($this->upcomingDays)
                ->startOfDay()
                ->timestamp;
        } else {
            $endTime = $endTime->addDays(config('parser.parsing_depth_after_today'))
                ->startOfDay()
                ->timestamp;
        }

        if ($endTime < $startTime) {
            return 0;
        }
    
        $query = Appointment::query()
            ->where('time', '>=', $startTime)
            ->where('time', '<=', $endTime)
            ->where('parsed_at', '<', $this->parsedAt);

        $appointmentCount = (clone $query)->count();
        if ($appointmentCount > self::MAX_DELETING_APPOINTMENTS_COUNT) {
            $ids = '';
            if ($appointmentCount <= 100) {
                $ids = (clone $query)->pluck('id')->implode(',');
            }

            with(new ExceptionNotificator())
                ->notifyAndSendToSentry(new AnErrorOccurred("Pay Attention! Too many appointments not parsed from OA ({$appointmentCount}). Incrementing 'not_found_count' are cancelled. {$ids}"));
            
            return;
        }

        $query->increment('not_found_count');

        $appointments = Appointment::query()
            ->select([
                'id',
                'time',
                'patients_id'
            ])
            ->where('not_found_count', '>', self::MAX_NOT_FOUND_COUNT)
            ->get();
        
        if ($appointments->count() > self::MAX_DELETING_APPOINTMENTS_COUNT) {
            with(new ExceptionNotificator())
                ->notifyAndSendToSentry(new AnErrorOccurred("Pay Attention! Too many appointments not parsed from OA ({$appointments->count()}). Deleting appointments from EHR are cancelled. Appointment IDs: " . $appointments->pluck('id')->implode(',')));
    
            return;
        }

        $insertData = [];
        foreach($appointments as $appointment) {
            $apptTime = Carbon::createFromTimestamp($appointment->time);
            $data = [
                'apptdate' => $apptTime->format('m/d/Y'),
                'appttime' => $apptTime->format('h:iA'),
            ];
            $comment = trans('comments.appointment_deleted_from_office_ally', $data);
            $insertData[] = [
                'comment' => $comment,
                'patient_id' => $appointment->patients_id,
            ];
        }
        PatientComment::bulkAddComments($insertData, true);
        $apptBuilder = Appointment::query()->whereIn('id', $appointments->pluck('id'));
        $apptBuilder->each(function ($appointment) {
            $appointment->update(['not_found_count' => 0]);
        });
        $deletedCount = $apptBuilder->delete();
        Bus::dispatchNow(new ClearAppointmentId($appointments->pluck('id')));

        echo "Deleted Appointment Count: $deletedCount" . PHP_EOL;
    }
}
