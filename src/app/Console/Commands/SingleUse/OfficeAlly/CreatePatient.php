<?php

namespace App\Console\Commands\SingleUse\OfficeAlly;

use Illuminate\Console\Command;
use App\Option;
use App\Patient;
use App\Provider;
use App\PatientInsurance;
use App\PatientInsurancePlan;
use App\Models\Language;
use App\Models\EligibilityPayer;
use App\Enums\PatientPreferredPhone;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use Carbon\Carbon;

class CreatePatient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oa:create-patient {patient-id}';

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
        $account = Option::OA_ACCOUNT_1;
        $officeAlly = new OfficeAllyHelper($account);

        $patient = Patient::findOrFail($this->argument('patient-id'));
        $preparedData = $this->prepareOAData($patient);

        $patientId = $officeAlly->createPatient($preparedData);

        if ($patientId) {
            $patient->patient_id = (int)$patientId;
            $patient->save();
        }
    }

    private function prepareOAData(Patient $patient)
    {
        $data = [
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'middle_initial' => $patient->middle_initial,
            'date_of_birth' => isset($patient->date_of_birth) ? Carbon::parse($patient->date_of_birth) : null,
            'preferred_language_id' => isset($patient->preferred_language_id) ? Language::whereId($patient->preferred_language_id)->first()->officeally_id : null,
            'sex' => $patient->sex,
            'email' => $patient->email,
            'cell_phone' => isset($patient->cell_phone) ? split_phone($this->formatPhone($patient->cell_phone)) : null,
            'home_phone' => isset($patient->home_phone) ? split_phone($this->formatPhone($patient->home_phone)) : null,
            'work_phone' => isset($patient->work_phone) ? split_phone($this->formatPhone($patient->work_phone)) : null,
            'preferred_phone' => isset($patient->preferred_phone) ? PatientPreferredPhone::$list[$patient->preferred_phone] : null,
            'address' => $patient->address,
            'address_2' => $patient->address_2,
            'city' => $patient->city,
            'state' => $patient->state,
            'zip' => $patient->zip,
            'primary_care_provider' => isset($patient->primary_provider_id) ? Provider::withTrashed()->whereKey($patient->primary_provider_id)->first()->officeally_id : null,
            'insurance_id' => isset($patient->primary_insurance_id) ? PatientInsurance::whereId($patient->primary_insurance_id)->first()->external_id : null,
            'mrn' => $patient->subscriber_id,
            'plan_name' => isset($patient->insurance_plan_id) ? PatientInsurancePlan::whereId($patient->insurance_plan_id)->first()->name : null,
            'visit_copay' => $patient->visit_copay,
            'eligibility_payer_id' => isset($patient->eligibility_payer_id) ? EligibilityPayer::whereId($patient->eligibility_payer_id)->first()->external_id : null,
            'diagnoses' => [],
            'billable_lines' => [],
        ];

        $diagnoses = $patient->diagnoses;
        foreach ($diagnoses as $diagnose) {
            $data['diagnoses'][] = [
                'code' => $diagnose->code,
                'description' => $diagnose->description,
            ];
        }

        $templates = $patient->templates;
        foreach ($templates as $template) {
            $temp = $template->toArray();
            if ($this->checkTemplateFields($temp)) {
                $data['billable_lines'][] = $temp;
            }
        }

        return $data;
    }

    private function formatPhone($phone)
    {
        return sprintf("(%s)-%s-%s",
            substr($phone, 0, 3),
            substr($phone, 3, 3),
            substr($phone, 6));
    }

    private function checkTemplateFields(array $template)
    {
        return !empty($template['pos']) || !empty($template['patient_insurances_procedure_id']) || !empty($template['modifier_a']) || !empty($template['modifier_b'])
            || !empty($template['modifier_c']) || !empty($template['modifier_d']) || !empty($template['charge']) || !empty($template['days_or_units']);
    }
}
