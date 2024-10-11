<?php

namespace App\Console\Commands;

use App\Events\NeedsWriteSystemCommentForPatientInquiry;
use Illuminate\Console\Command;
use App\Status;
use App\Models\Patient\Inquiry\PatientInquiry;
use Carbon\Carbon;

class CloseCompletedInquiries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inquiries:close-completed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();
        $lastAppointmentQuery = 'SELECT FROM_UNIXTIME(appointments.time) FROM appointments WHERE appointments.patients_id = patient_inquiries.inquirable_id AND appointment_statuses_id IN (' . implode(",", [$completedId, $visitCreatedId]) . ') AND appointments.deleted_at IS NULL ORDER BY appointments.time DESC LIMIT 1';

        PatientInquiry::query()
            ->select([
                'patient_inquiries.*',
                \DB::raw("($lastAppointmentQuery) AS last_appointment_date")
            ])
            ->active()
            ->wherePatientIsCreated()
            ->havingRaw('last_appointment_date IS NOT NULL AND last_appointment_date > created_at')
            ->each(function ($inquiry) {
                $inquiry->update(['closed_at' => Carbon::now()]);

                event(new NeedsWriteSystemCommentForPatientInquiry(
                    $inquiry->id,
                    trans('comments.patient_inquiry_was_completed')
                ));
            });
    }
}
