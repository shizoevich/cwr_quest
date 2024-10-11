<?php

namespace App\Jobs;

use App\Models\TreatmentModality;
use App\Patient;
use App\PatientInsurancePlanProcedure;
use App\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CalculateFeePerVisit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Patient
     */
    private $patient;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var TreatmentModality
     */
    private $treatmentModality;

    public function __construct(Patient $patient, Provider $provider, TreatmentModality $treatmentModality)
    {
        $this->patient = $patient;
        $this->provider = $provider;
        $this->treatmentModality = $treatmentModality;
    }

    public function handle()
    {
        $patientInsurancePlan = $this->patient->insurancePlan;

        if (empty($patientInsurancePlan)) {
            return null;
        }

        $tariffPlanId = $this->provider->tariffPlan->id;
        $insuranceProcedureId = $this->treatmentModality->insurance_procedure_id;
        $patientInsurancePlanId = $patientInsurancePlan->parent_id ?? $patientInsurancePlan->id;

        $insurancePlanProcedure = PatientInsurancePlanProcedure::query()
            ->where('tariff_plan_id', $tariffPlanId)
            ->where('procedure_id', $insuranceProcedureId)
            ->where('plan_id', $patientInsurancePlanId)
            ->first();

        $feePerVisit = null;

        if (isset($insurancePlanProcedure)) {
            $feePerVisit = $this->treatmentModality->is_telehealth ? $insurancePlanProcedure->telehealth_price : $insurancePlanProcedure->price;
        }

        return $feePerVisit;
    }
}
