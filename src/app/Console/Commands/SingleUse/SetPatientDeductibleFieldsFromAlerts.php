<?php

namespace App\Console\Commands\SingleUse;

use App\PatientStatus;
use App\Patient;
use App\PatientAlert;
use App\Option;
use App\Helpers\RetryJobQueueHelper;
use Illuminate\Console\Command;

class SetPatientDeductibleFieldsFromAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:set-patient-deductible-fields';

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
        $archivedId = PatientStatus::getArchivedId();
        $dischargedId = PatientStatus::getDischargedId();

        Patient::query()
            ->where('is_test', 0)
            ->whereNotIn('status_id', [$archivedId, $dischargedId])
            ->chunk(1000, function ($patients) {
                foreach ($patients as $patient) {
                    $alert = PatientAlert::query()
                        ->where('patient_id', $patient->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (isset($alert) && $alert->deductible > 0) {
                        $patient->update([
                            'deductible' => $alert->deductible,
                            'deductible_met' => $alert->deductible_met,
                            'deductible_remaining' => $alert->deductible_remaining,
                            'insurance_pay' => $alert->insurance_pay,
                        ]);
                        $dataToUpdate = [
                            'deductible' => $alert->deductible,
                        ];
                        
                        RetryJobQueueHelper::dispatchRetryUpdatePatient(Option::OA_ACCOUNT_1, $dataToUpdate, $patient->id);
                    }
                }
            });
    }
}
