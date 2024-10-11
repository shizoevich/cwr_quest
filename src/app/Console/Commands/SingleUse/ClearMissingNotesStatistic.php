<?php

namespace App\Console\Commands\SingleUse;

use App\Appointment;
use App\Status;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class ClearMissingNotesStatistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missing-notes:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
    
        $appointments = Appointment::query()
            ->select([
                'appointments.id'
            ])
            ->leftJoin('patient_notes', function(JoinClause $join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('appointments.note_on_paper', '=', 0)
            ->where('appointments.is_initial', '=', 0)
            ->whereNull('appointments.initial_assessment_id')
            ->where(function(Builder $query) {
                $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
            })
            ->where('appointments.time', '<=', Carbon::parse('2018-12-31')->endOfDay()->timestamp)
            ->groupBy(['appointments.id'])
            ->get()
            ->pluck('id')
            ->toArray();
        Appointment::query()
            ->whereIn('id', $appointments)
            ->each(function ($appointment) {
                $appointment->update(['note_on_paper' => 1]);
            });
        $this->output->success(count($appointments) . ' missing notes was cleared.');
        $this->output->text(implode(',', $appointments));
    }
}
