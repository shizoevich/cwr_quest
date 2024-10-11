<?php

namespace App\Console\Commands\SingleUse;

use App\Appointment;
use App\Jobs\Salary\SyncSalaryData;
use App\Models\Provider\Salary;
use App\PatientNote;
use App\PatientVisit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FixSalary extends Command
{
    private $billingPeriodStartDate;
    private $billingPeriodEndDate;
    
    private $extensionStartDate;
    private $extensionEndDate;

    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:fix';

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
        $this->billingPeriodStartDate = Carbon::parse('2020-10-12')->startOfDay();
        $this->billingPeriodEndDate = Carbon::parse('2020-10-25')->endOfDay();
        
        $this->extensionStartDate = Carbon::parse('2020-10-26')->startOfDay();
        $this->extensionEndDate = Carbon::parse('2020-10-27')->endOfDay();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $patientNotes = $this->getPatientNotes();
        $this->updatePatientNotes($patientNotes);
        
        $appointmentsWithInitialAssessments = $this->getAppointmentsWithInitialAssessments();
        $this->updateAppointmentsWithInitialAssessments($appointmentsWithInitialAssessments);
        
        $appointmentIds = array_unique(array_merge($patientNotes->pluck('appointment_id')->toArray(), $appointmentsWithInitialAssessments->pluck('id')->toArray()));
        $this->deleteSalaryRefunds($appointmentIds);
        $this->updateVisits($appointmentIds);
        \Bus::dispatchNow(new SyncSalaryData());
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    private function getPatientNotes()
    {
        return PatientNote::query()
            ->select('patient_notes.*')
            ->join('appointments', 'appointments.id', '=', 'patient_notes.appointment_id')
            ->whereNotNull('patient_notes.finalized_at')
            ->where('patient_notes.is_finalized', 1)
            ->whereDate('patient_notes.finalized_at', '>=', $this->extensionStartDate->toDateString())
            ->whereDate('patient_notes.finalized_at', '<=', $this->extensionEndDate->toDateString())
            ->where('appointments.time', '<=', $this->billingPeriodEndDate->timestamp)
            ->get();
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|Collection
     */
    private function getAppointmentsWithInitialAssessments()
    {
        return Appointment::query()
            ->whereNotNull('initial_assessment_created_at')
            ->whereDate('initial_assessment_created_at', '>=', $this->extensionStartDate->toDateString())
            ->whereDate('initial_assessment_created_at', '<=', $this->extensionEndDate->toDateString())
            ->where('time', '<=', $this->billingPeriodEndDate->timestamp)
            ->get();
    }
    
    /**
     * @param Collection $patientNotes
     */
    private function updatePatientNotes(Collection $patientNotes)
    {
        PatientNote::query()
            ->whereKey($patientNotes->pluck('id'))
            ->each(function($patientNote) {
                $patientNote->update([
                    'finalized_at' => $this->billingPeriodEndDate->toDateTimeString(),
                ]);
            });
    }
    
    /**
     * @param Collection $appointments
     */
    private function updateAppointmentsWithInitialAssessments(Collection $appointments)
    {
        Appointment::query()->whereKey($appointments->pluck('id'))->update([
            'initial_assessment_created_at' => $this->billingPeriodEndDate->toDateTimeString(),
        ]);
    }
    
    /**
     * @param array $appointmentIds
     */
    private function deleteSalaryRefunds(array $appointmentIds)
    {
        if(empty($appointmentIds)) {
            return;
        }
        $visitIds = PatientVisit::query()->whereIn('appointment_id', $appointmentIds)->pluck('id')->toArray();
        Salary::query()->whereIn('visit_id', $visitIds)->whereIn('type', Salary::REFUND_TYPES)->delete();
    }
    
    /**
     * @param array $appointmentIds
     */
    private function updateVisits(array $appointmentIds)
    {
        if(empty($appointmentIds)) {
            return;
        }
        PatientVisit::query()
            ->whereIn('appointment_id', $appointmentIds)
            ->each(function ($patientVisit) {
                $patientVisit->update(['needs_update_salary' => 1]);
            });
    }
}
