<?php

namespace App\Console\Commands\SingleUse;

use App\Patient;
use App\Models\Patient\Inquiry\PatientInquiryStage;
use Illuminate\Console\Command;

class RestoreActivePatientsWithFourAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore-inquiries:active-patients-with-four-appointments';

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
        $patients = Patient::query()
            ->select([
                'patients.id',
                \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
                \DB::raw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` IN (1, 7) AND `appointments`.`deleted_at` IS NULL) AS visits_count'),
            ])
            ->where('patients.is_test', '=', 0)
            ->where('patients.status_id', 1)
            ->havingRaw('visits_count = 4')
            ->havingRaw("patient_created_at >= DATE('2024-01-01')")
            ->get();

        foreach ($patients as $patient) {
            $patientInquiry = $patient->inquiries()
                ->orderByDesc('created_at')
                ->first();
            
            if (isset($patientInquiry) && $patientInquiry->closed_at) {
                $patientInquiry->update([
                    'closed_at' => null,
                    'stage_id' => PatientInquiryStage::getFourAppointmentsCompleteId(),
                    'stage_changed_at' => now(),
                ]);
            }
        }
    }
}
