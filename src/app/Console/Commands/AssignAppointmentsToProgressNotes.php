<?php

namespace App\Console\Commands;

use App\PatientNote;
use App\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AssignAppointmentsToProgressNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:assign';

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
     * @return void
     */
    public function handle()
    {
        $visitCreatedStatus = Status::getVisitCreatedId();
        $cancelStatuses = Status::getOtherCancelStatusesId();
        $cancelStatuses = '(' . implode(',', $cancelStatuses) . ')';
        $baseFilter = 'AND appointments.appointment_statuses_id';
        $filters = [
            $baseFilter . ' = ' . $visitCreatedStatus,
            $baseFilter . ' NOT IN ' . $cancelStatuses
        ];
        $queries = [
            "SET time_zone = '-07:00'",
            "SET time_zone = '+00:00'"
        ];
        foreach ($filters as $filter) {
            foreach ($queries as $query) {
                \DB::statement($query);
                $this->attach($filter);
            }
        }
    }

    private function attach($filters = '')
    {

        \DB::statement("
            UPDATE patient_notes
            #JOIN providers ON providers.provider_name = patient_notes.provider_name
            JOIN appointments ON DATE(FROM_UNIXTIME(appointments.TIME)) = patient_notes.date_of_service 
            SET patient_notes.appointment_id = appointments.id
            WHERE patient_notes.appointment_id IS NULL
                AND patient_notes.date_of_service IS NOT NULL 
                AND patient_notes.patients_id = appointments.patients_id
                {$filters}
                AND appointments.deleted_at IS NULL
                #AND patient_notes.provider_id = appointments.providers_id
        ");
    }
}
