<?php

namespace App\Console\Commands\SalaryCheck;

use App\Appointment;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckPatientsWithProgressNoteInsteadOfInitialAssessment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-salary:check-patients-with-pn-instead-of-ia';
    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check patients without initial assessment';
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
        $billingPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);

        $startTimestamp = Carbon::parse($billingPeriod->start_date)->subWeek()->timestamp;

        $appointments = Appointment::query()
            ->with([
                'patient',
                'patient.documents' => function ($query) {
                    $query->initialAssessment();
                }
            ])
            ->where('time', '>=', $startTimestamp)
            ->where('is_initial', 1)
            ->whereNull('initial_assessment_id')
            ->whereHas('patientNote')
            ->orderBy('time', 'desc')
            ->get();

        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            $initialAssessment = $patient->documents->first();
            $date = Carbon::createFromTimestamp($appointment->time)->toDateTimeString();

            $this->info('Appt: ' . $appointment->id . ', ' . $date . '; Pt: ' . $patient->id . ' ' . $patient->getFullName() . ';' . ($initialAssessment ? ' Has IA;' : ''));
        }
    }
}
