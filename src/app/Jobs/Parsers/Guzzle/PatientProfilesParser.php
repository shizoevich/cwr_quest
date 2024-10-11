<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\PatientProfileDTO;
use App\Events\NeedsWriteSystemComment;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Patients\DeletePatientProvider;
use App\Models\Diagnose;
use App\Models\EligibilityPayer;
use App\Models\Language;
use App\Models\PatientHasProvider;
use App\Option;
use App\Patient;
use App\PatientDiagnoseOld;
use App\PatientInsurance;
use App\PatientInsurancePlan;
use App\PatientStatus;
use App\Provider;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

/**
 * Class PatientProfilesParser
 * @package App\Jobs\Parsers\Guzzle
 */
class PatientProfilesParser extends AbstractParser
{
    protected $patientIds;

    private $sleep;

    private $newPatientStatusId;

    private $languages = [];

    /**
     * PatientProfilesParser constructor.
     *
     * @param array $patientIds
     * @param int   $sleep
     */
    public function __construct(array $patientIds, int $sleep = 0)
    {
        $this->patientIds = $patientIds;
        $this->sleep = $sleep;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_3);

        $this->newPatientStatusId = PatientStatus::getNewId();

        $this->languages = Language::query()
            ->whereNotNull('officeally_id')
            ->pluck('id', 'officeally_id')
            ->toArray();

        $savedPatients = 0;
        foreach ($this->patientIds as $patientId) {
            $profilePage = $officeAllyHelper->getPatientProfile($patientId);

            $this->patientCrawler($profilePage);

            $savedPatients++;

            if ($this->sleep > 0) {
                sleep($this->sleep);
            }
        }

        if ($savedPatients < count($this->patientIds)) {
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred(sprintf('Parsed %d patients, but saved %d', count($this->patientIds), $savedPatients)));
        }

        \Artisan::call('patients:update-statuses', ['--sync' => true]);
    }

    private function patientCrawler($profilePage)
    {
        $crawler = new Crawler($profilePage);

        $patient = $this->parsePatient($crawler);
        if (!$patient) {
            return;
        }
        if (!$patient->status_id) {
            $patient->update(['status_id' => $this->newPatientStatusId]);
        }
        $this->attachProvider($crawler, $patient);
        $this->parseDiagnosis($crawler, $patient);
        $this->parseTemplates($crawler, $patient);
        $this->parseInsurances($crawler, $patient);
    }

    /**
     * Code was copied from old crawler class
     *
     * @param Crawler $crawler
     * @param Patient $patient
     */
    private function parseInsurances(Crawler $crawler, Patient $patient)
    {
        $insuranceName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceName')->first()->attr('value');
        if ($insuranceName != '') {
            $insurance = PatientInsurance::firstOrCreate([
                'insurance' => $insuranceName,
            ]);

            $insuranceExternalId = $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceID')->first()->attr('value') ?? null;
            if (isset($insuranceExternalId) && empty($insurance->external_id)) {
                $insurance->update([
                    'external_id' => $insuranceExternalId,
                ]);
            }

            $patient->primary_insurance_id = $insurance->id;
            $patient->primary_insurance = $insurance->insurance;

            $insurancePlanName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsurancePlanName')->first()->attr('value');
            if ($insurancePlanName != '') {
                $insurancePlan = PatientInsurancePlan::firstOrCreate([
                    'insurance_id' => $insurance->id,
                    'name' => $insurancePlanName,
                ]);

                // $authorizationNumber = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_PriorAuthorizationNumber2')->first()->attr('value');
                // if ($authorizationNumber) {
                //     $insurancePlan->update(['is_verification_required' => true]);
                // }

                $patient->insurance_plan_id = $insurancePlan->id;
            }
        }

        $secondaryInsuranceName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SecondaryInsuranceName')->first()->attr('value');
        if ($secondaryInsuranceName != '') {
            $secondaryInsurance = PatientInsurance::firstOrCreate([
                'insurance' => $secondaryInsuranceName,
            ]);
            $secondaryInsurancePlanName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SecondaryInsurancePlanName')->first()->attr('value');
            if ($secondaryInsurancePlanName != '') {
                $secondaryInsurancePlan = PatientInsurancePlan::firstOrCreate([
                    'insurance_id' => $secondaryInsurance->id,
                    'name' => $secondaryInsurancePlanName,
                ]);
            }
        }

        $eligibilityPayerId = $crawler->filter('input#ctl00_phFolderContent_ucPatient_BatchEligibilityPayerID')->first()->attr('value');
        $eligibilityPayerName = $crawler->filter('input#ctl00_phFolderContent_ucPatient_BatchEligibilityPayerName')->first()->attr('value');
        if ($eligibilityPayerId && $eligibilityPayerName) {
            $eligibilityPayer = EligibilityPayer::query()->updateOrCreate([
                'external_id' => $eligibilityPayerId,
            ], [
                'name' => $eligibilityPayerName,
            ]);
            $patient->eligibility_payer_id = $eligibilityPayer->id;
        } else {
            $patient->eligibility_payer_id = null;
        }

        $patient->save();
    }

    /**
     * Code was copied from old crawler class
     *
     * @param Crawler $crawler
     * @param Patient $patient
     */
    private function parseTemplates(Crawler $crawler, Patient $patient)
    {
        $templates = $crawler->filter('input#ctl00_phFolderContent_ucPatient_ucPATemplateLineItem_hdnJsLoadTemplateLineItem')->first()->attr('value');
        $templates = json_decode($templates, true);
        if (!$templates || !is_array($templates)) {
            return;
        }

        $positions = array_pluck($templates, 'PatientTemplateLineItemNo');
        $patient->templates()
            ->whereNotIn('position', $positions)
            ->each(function ($template) {
                $template->delete();
            });
        foreach ($templates as $template) {
            $position = __data_get($template, 'PatientTemplateLineItemNo') ? (int)$template['PatientTemplateLineItemNo'] : null;
            if (empty($position)) {
                continue;
            }
            $patientProfileDTO = new PatientProfileDTO([
                'position' => $position,
                'pos' => __data_get($template, 'PatientTemplatePlaceOfService'),
                'cpt' => __data_get($template, 'PatientTemplateCPT'),
                'modifier_a' => __data_get($template, 'PatientTemplateModifierA'),
                'modifier_b' => __data_get($template, 'PatientTemplateModifierB'),
                'modifier_c' => __data_get($template, 'PatientTemplateModifierC'),
                'modifier_d' => __data_get($template, 'PatientTemplateModifierD'),
                'diagnose_pointer' => __data_get($template, 'PatientTemplateDiagnosisCode'),
                'charge' => !empty(data_get($template, 'PatientTemplateCharge')) ? (float)$template['PatientTemplateCharge'] : null,
                'days_or_units' => !empty(data_get($template, 'PatientTemplateQuantity')) ? (int)$template['PatientTemplateQuantity'] : null,
            ]);

            $patient->templates()->updateOrCreate(
                ['position' => $patientProfileDTO->position],
                $patientProfileDTO->toArray()
            );
        }
    }

    /**
     * @param Crawler $crawler
     *
     * @return Patient|null
     */
    private function parsePatient(Crawler $crawler)
    {
        try {
            $patientId = $crawler->filter('#ctl00_phFolderContent_ucPatient_lblPatientID')->first()->text();
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        if (!$patientId) {
            return null;
        }
        $patientData = [
            'first_name'           => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblFirstName')->first()->text(),
            'last_name'            => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblLastName')->first()->text(),
            'middle_initial'       => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblMiddleName')->first()->text(),
            'sex'                  => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblGender')->first()->text(),
            'date_of_birth'        => $this->getDateOfBirth($crawler),
            'created_patient_date' => $this->getCreatedPatientDate($crawler),
            'auth_number'          => $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_PriorAuthorizationNumber2')->first()->attr('value'),
            'eff_start_date'       => $this->getEffStartDate($crawler),
            'eff_stop_date'        => $this->getEffStopDate($crawler),
            'subscriber_id'        => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceSubscriberID')->first()->attr('value'),
            'primary_insurance'    => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceName')->first()->attr('value'),
            'deductible'           => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceDeductible')->first()->attr('value') ?? 0,
            'visit_copay'          => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceVisitCopay')->first()->attr('value') ?? 0,
            'visits_auth'          => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsAuthorized')->first()->attr('value')),
            'visits_auth_left'     => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsLeft')->first()->attr('value')),
            'city' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_City')->first()->attr('value'),
            'state' => $crawler->filter('#ctl00_phFolderContent_ucPatient_lstState option:selected')->first()->attr('value'),
            'address' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine1')->first()->attr('value'),
            'address_2' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine2')->first()->attr('value'),
            'zip' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_Zip')->first()->attr('value'),
        ];

        // $patientDataDTO = new PatientProfileDTO([
        //     'first_name'           => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblFirstName')->first()->text(),
        //     'last_name'            => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblLastName')->first()->text(),
        //     'middle_initial'       => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblMiddleName')->first()->text(),
        //     'sex'                  => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblGender')->first()->text(),
        //     'date_of_birth'        => $this->getDateOfBirth($crawler),
        //     'created_patient_date' => $this->getCreatedPatientDate($crawler),
        //     'auth_number'          => $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_PriorAuthorizationNumber2')->first()->attr('value'),
        //     'eff_start_date'       => $this->getEffStartDate($crawler),
        //     'eff_stop_date'        => $this->getEffStopDate($crawler),
        //     'subscriber_id'        => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceSubscriberID')->first()->attr('value'),
        //     'primary_insurance'    => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceName')->first()->attr('value'),
        //     'visit_copay'          => (integer) $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceVisitCopay')->first()->attr('value') ?? 0,
        //     'visits_auth'          => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsAuthorized')->first()->attr('value')),
        //     'visits_auth_left'     => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsLeft')->first()->attr('value')),
        //     'city'                 => $crawler->filter('input#ctl00_phFolderContent_ucPatient_City')->first()->attr('value'),
        //     'state'                => $crawler->filter('#ctl00_phFolderContent_ucPatient_lstState option:selected')->first()->attr('value'),
        //     'address'              => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine1')->first()->attr('value'),
        //     'address_2'            => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine2')->first()->attr('value'),
        //     'zip'                  => $crawler->filter('input#ctl00_phFolderContent_ucPatient_Zip')->first()->attr('value'),
        // ]);

        // $patientData = $patientDataDTO->toArray();

        $externalLanguageId = $crawler->filter('#ctl00_phFolderContent_ucPatient_ddlLanguage option:selected')->first()->attr('value');
        if (!$externalLanguageId) {
            $patientData['preferred_language_id'] = null;
        } else if (array_key_exists($externalLanguageId, $this->languages)) {
            $patientData['preferred_language_id'] = $this->languages[$externalLanguageId];
        }

        $patient = Patient::query()->where('patient_id', $patientId)->first();
        if (!$patient) {
            $patientData['cell_phone'] = $this->getCellPhone($crawler);
            $patientData['home_phone'] = $this->getHomePhone($crawler);
            $patientData['work_phone'] = $this->getWorkPhone($crawler);
            $patientData['patient_id'] = $patientId;

            return Patient::create($patientData);
        } else {
            if ($patient->parse_cell_phone) {
                $patientData['cell_phone'] = $this->getCellPhone($crawler);
            }
            if ($patient->parse_home_phone) {
                $patientData['home_phone'] = $this->getHomePhone($crawler);
            }
            if ($patient->parse_work_phone) {
                $patientData['work_phone'] = $this->getWorkPhone($crawler);
            }
            $patient->update($patientData);

            return $patient->refresh();
        }
    }

    /**
     * Code was copied from old crawler class
     *
     * @param Crawler $crawler
     * @param Patient $patient
     */
    private function parseDiagnosis(Crawler $crawler, Patient $patient)
    {
        $diagnose = "";
        $patientDiagnoses = [];
        for ($i = 1; $i <= 12; $i++) {
            $tmp = trim($crawler->filter('input#ctl00_phFolderContent_ucPatient_ucDiagnosisCodes_dd_10_' . $i)->first()->attr('value'));
            $diagnoseCode = trim($crawler->filter('input#ctl00_phFolderContent_ucPatient_ucDiagnosisCodes_dc_10_' . $i)->first()->attr('value'));
            if (!empty($tmp)) {
                $diagnose .= "\"";
                if (!empty($diagnoseCode)) {
                    $diagnose .= $diagnoseCode . " - ";
                }
                $diagnose .= $tmp . "\",";
            }
            if (!empty($tmp) && !empty($diagnoseCode) && (starts_with($diagnoseCode, 'F') || starts_with($diagnoseCode, 'Z'))) {
                $diagnoseModel = Diagnose::query()->firstOrCreate(['code' => $diagnoseCode], ['description' => $tmp]);
                $patientDiagnoses[] = $diagnoseModel->getKey();
            }
        }
        $stats = $patient->diagnoses()->sync($patientDiagnoses);
        if (count($stats['attached']) > 0 || count($stats['detached']) > 0) {
            event(new NeedsWriteSystemComment($patient->id, trans('comments.diagnose_changed_in_oa')));
        }
        if (!empty($diagnose)) {
            $diagnose = trim($diagnose, ',');
            //@todo delete PatientDiagnoseOld
            PatientDiagnoseOld::updateOrCreate(['patient_officeally_id' => $patient->patient_id], ['diagnose' => $diagnose]);
        }
    }

    /**
     * Code was copied from old crawler class
     *
     * @param Crawler $crawler
     * @param Patient $patient
     */
    private function attachProvider(Crawler $crawler, Patient $patient)
    {
        $providerName = trim(strip_tags($crawler->filter('input#ctl00_phFolderContent_ucPatient_tbxPrimaryProvider')->first()->attr('value')));

        if ($providerName) {
            $provider = Provider::withTrashed()->firstOrCreate(['provider_name' => $providerName]);
            if ($patient->id != 1111) { //1111 - Test Patient
                \Bus::dispatchNow(new DeletePatientProvider($patient, [$provider->id]));
            }
            $comment = trans('comments.provider_assigned_automatically', [
                'provider_name' => $provider->provider_name,
            ]);

            if ($patient->allProviders()->withTrashed()->where('id', $provider->id)->count() === 0) {
                PatientHasProvider::create([
                    'patients_id' => $patient->id,
                    'providers_id' => $provider->id
                ]);

                event(new NeedsWriteSystemComment($patient->id, $comment));
            } else {
                $patientHasProvider = PatientHasProvider::where('providers_id', $provider->id)
                    ->where('patients_id', $patient->id)
                    ->first();
                $count = $patientHasProvider->update(['chart_read_only' => false]);

                if ($count > 0) {
                    event(new NeedsWriteSystemComment($patient->id, $comment));
                }
            }
            $patient->primary_provider_id = $provider->getKey();
        } else {
            $patient->primary_provider_id = null;
        }
        $patient->save();
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private function getDateOfBirth(Crawler $crawler)
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_DOB_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_DOB_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_DOB_Year')->first()->attr('value');

        return $this->createDateFromParts($day, $month, $year);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private function getCreatedPatientDate(Crawler $crawler)
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_SignatureOnFileDate_Year')->first()->attr('value');

        return $this->createDateFromParts($day, $month, $year);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private function getEffStartDate(Crawler $crawler)
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStartDate_Year')->first()->attr('value');

        return $this->createDateFromParts($day, $month, $year);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private function getEffStopDate(Crawler $crawler)
    {
        $day = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Day')->first()->attr('value');
        $month = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Month')->first()->attr('value');
        $year = $crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_AuthorizedStopDate_Year')->first()->attr('value');

        return $this->createDateFromParts($day, $month, $year);
    }

    /**
     * @param $day
     * @param $month
     * @param $year
     *
     * @return string|null
     */
    private function createDateFromParts($day, $month, $year)
    {
        $date = null;
        if (!empty($day) && !empty($month) && !empty($year)) {
            try {
                $date = Carbon::create($year, $month, $day)->toDateString();
            } catch (\InvalidArgumentException $e) {
                $date = null;
            }
        }

        return $date;
    }

    /**
     * @param $areaCode
     * @param $prefix
     * @param $number
     *
     * @return string|null
     */
    private function createPhoneFromParts($areaCode, $prefix, $number)
    {
        $phone = null;
        if (!empty($areaCode) && !empty($prefix) && !empty($number)) {
            $phone = "{$areaCode}-{$prefix}-{$number}";
        }

        return $phone;
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private function getHomePhone(Crawler $crawler)
    {
        $areaCode = $crawler->filter('input#ctl00_phFolderContent_ucPatient_HomePhone_AreaCode')->first()->attr('value');
        $prefix = $crawler->filter('input#ctl00_phFolderContent_ucPatient_HomePhone_Prefix')->first()->attr('value');
        $number = $crawler->filter('input#ctl00_phFolderContent_ucPatient_HomePhone_Number')->first()->attr('value');

        return $this->createPhoneFromParts($areaCode, $prefix, $number);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private function getCellPhone(Crawler $crawler)
    {
        $areaCode = $crawler->filter('input#ctl00_phFolderContent_ucPatient_CellPhone_AreaCode')->first()->attr('value');
        $prefix = $crawler->filter('input#ctl00_phFolderContent_ucPatient_CellPhone_Prefix')->first()->attr('value');
        $number = $crawler->filter('input#ctl00_phFolderContent_ucPatient_CellPhone_Number')->first()->attr('value');

        return $this->createPhoneFromParts($areaCode, $prefix, $number);
    }

    /**
     * @param Crawler $crawler
     *
     * @return string|null
     */
    private function getWorkPhone(Crawler $crawler)
    {
        $areaCode = $crawler->filter('input#ctl00_phFolderContent_ucPatient_WorkPhone_AreaCode')->first()->attr('value');
        $prefix = $crawler->filter('input#ctl00_phFolderContent_ucPatient_WorkPhone_Prefix')->first()->attr('value');
        $number = $crawler->filter('input#ctl00_phFolderContent_ucPatient_WorkPhone_Number')->first()->attr('value');

        return $this->createPhoneFromParts($areaCode, $prefix, $number);
    }
}
