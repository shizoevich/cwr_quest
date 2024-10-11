<?php

namespace App\Console\Commands\SingleUse;

use App\PatientStatus;
use App\Patient;
use App\PatientInsurance;
use Illuminate\Console\Command;

class SetPatientSelfPayFieldsFromInsurance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:set-patient-self-pay-fields';

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
        $cashId = PatientInsurance::getCashId();

        Patient::query()
            ->select(['patients.*', 'patient_insurances_plans.name AS plan_name'])
            ->where('is_test', 0)
            ->whereNotIn('status_id', [$archivedId, $dischargedId])
            ->where('primary_insurance_id', $cashId)
            ->leftJoin('patient_insurances_plans', 'patient_insurances_plans.id', 'patients.insurance_plan_id')
            ->chunk(1000, function ($patients) {
                foreach ($patients as $patient) {
                    $matches = [];
                    preg_match('/(\d+)/', $patient->plan_name, $matches);
                    $selfPayValue = isset($matches[1]) ? (int) $matches[1] : 0;

                    $patient->update([
                        'is_self_pay' => true,
                        'self_pay' => $selfPayValue
                    ]);
                }
            });
    }
}
