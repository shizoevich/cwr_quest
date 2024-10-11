<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\PatientVisitDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Salary\SyncSalaryData;
use App\Models\Diagnose;
use App\Models\Patient\Visit\PatientVisitDiagnose;
use App\Option;
use App\Patient;
use App\PatientInsurance;
use App\PatientInsurancePlan;
use App\PatientInsuranceProcedure;
use App\PatientVisit;
use App\Provider;
use App\Appointment;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

/**
 * Copied from \App\Jobs\Parsers\Puppeteer\PatientVisitsInfoParser
 * Class PatientVisitsInfoParser
 * @package App\Jobs\Parsers\Guzzle
 */
class PatientVisitsInfoParser extends AbstractParser
{
    protected $visitsIds;

    protected $isSalaryParser;

    /**
     * Create a new job instance.
     *
     * @param        $visitsIds
     * @param bool   $isSalaryParser
     */
    public function __construct(
        $visitsIds,
        $isSalaryParser = false
    ) {
        $this->visitsIds = $visitsIds;
        $this->isSalaryParser = $isSalaryParser;
        parent::__construct();
    }

    public function handleParser()
    {
        try {
            $savedVisits = $this->parseVisits();
            if ($savedVisits < count($this->visitsIds)) {
                with(new ExceptionNotificator())->officeAllyNotifyAndSendToSentry(new AnErrorOccurred(sprintf(
                    'Parsed %d visit pages, but saved %d',
                    count($this->visitsIds),
                    $savedVisits
                )));
            }
        } finally {
            if ($this->isSalaryParser) {
                Option::setOptionValue('parsing_visits', 0);
                dispatch(new SyncSalaryData());
            }
        }
    }

    private function parseVisits()
    {
        $savedVisits = 0;
        $officeAlly = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_2);
        foreach ($this->visitsIds as $visitId) {
            $visitPage = $officeAlly->getVisitInfo($visitId);
            $crawler = new Crawler($visitPage);

            //      $visitData = [
            //         'visit_id'    => $visitId,
            //         'provider_id' => $this->getProviderId($crawler),
            //         'is_telehealth'   => $this->getIsTelehealth($crawler),
            //         'copay'       => $crawler->filter('input#ctl00_phFolderContent_InsuranceVisitCopay')->first()->attr('value'),
            //         'patient_id'  => $this->getPatientId($crawler),

            //         // type conversion to integer is used to prevent saving of unnecessary log
            //         'is_cash'     => (int)($crawler->filter('select#ctl00_phFolderContent_lstStatus  option:selected')->first()->text() === 'Cash Payment'),
            //     ] + $this->getInsuranceData($crawler) + $this->getBillingData($crawler);

            //    $visit = PatientVisit::updateOrCreate(array_only($visitData, 'visit_id'), $visitData);
            $visitDataDTO = new PatientVisitDTO([
                'visit_id' => $visitId,
                'provider_id' => $this->getProviderId($crawler),
                'is_telehealth' => (int) $this->getIsTelehealth($crawler),
                'copay' => (float) $crawler->filter('input#ctl00_phFolderContent_InsuranceVisitCopay')->first()->attr('value'),
                'patient_id' => $this->getPatientId($crawler),
                'is_cash' => (int) ($crawler->filter('select#ctl00_phFolderContent_lstStatus  option:selected')->first()->text() === 'Cash Payment'),
            ]);

            $visitData = $visitDataDTO->toArray();
            $visitData = $visitData + $this->getInsuranceData($crawler) + $this->getBillingData($crawler);
            $visit = PatientVisit::updateOrCreate(array_only($visitData, 'visit_id'), $visitData);

            $this->parseDiagnoses($crawler, $visit);
            $savedVisits++;
        }

        return $savedVisits;
    }

    /**
     * @param Crawler      $crawler
     * @param PatientVisit $visit
     */
    private function parseDiagnoses(Crawler $crawler, PatientVisit $visit)
    {
        $diagnoses = [];
        for ($i = 1; $i <= 12; $i++) {
            $code = trim($crawler->filter('#ctl00_phFolderContent_ucDiagnosisCodes_dc_10_' . $i)->first()->attr('value'));
            $description = trim($crawler->filter('#ctl00_phFolderContent_ucDiagnosisCodes_dd_10_' . $i)->first()->attr('value'));
            if ($code && $description) {
                $diagnose = Diagnose::query()->firstOrCreate(['code' => $code], ['description' => $description]);
                $diagnoses[] = $diagnose->id;
            }
        }
        $diagnoses = array_unique($diagnoses);
        $this->syncPatientVisitDiagnoses($visit, $diagnoses);
    }

    /**
     * @param Crawler $crawler
     *
     * @return mixed|null
     */
    private function getPatientId(Crawler $crawler)
    {
        $patientId = $crawler->filter('input#ctl00_phFolderContent_PatientID')->first()->attr('value');

        return data_get(Patient::query()->where('patient_id', $patientId)->first(), 'id');
    }

    /**
     * @param Crawler $crawler
     *
     * @return mixed|null
     */
    private function getProviderId(Crawler $crawler)
    {
        $providerId = $crawler->filter('input#ctl00_phFolderContent_ProviderID')->first()->attr('value');

        return data_get(Provider::query()->where('officeally_id', $providerId)->first(), 'id');
    }

    /**
     * @param Crawler $crawler
     *
     * @return mixed|null
     */
    private function getIsTelehealth(Crawler $crawler)
    {
        $visitReason = $crawler->filter('input#ctl00_phFolderContent_ReasonForVisit')->first()->attr('value');
        return str_contains($visitReason, Appointment::REASON_TELEHEALTH);
    }

    /**
     * Copied from old crawler
     *
     * @param Crawler $crawler
     *
     * @return array
     */
    private function getInsuranceData(Crawler $crawler)
    {
        $insuranceName = $crawler->filter('input#ctl00_phFolderContent_InsuranceName')->first()->attr('value');
        $result = [
            'insurance_id' => null,
            'plan_id'      => null,
        ];
        if (trim($insuranceName) != '') {
            $insurance = PatientInsurance::firstOrCreate(['insurance' => $insuranceName]);
            $insurancePlanName = $crawler->filter('input#ctl00_phFolderContent_InsurancePlanName')->first()->attr('value');
            if ($insurancePlanName != '') {
                $insurancePlan = PatientInsurancePlan::firstOrCreate([
                    'insurance_id' => $insurance->id,
                    'name'         => $insurancePlanName
                ]);
                $result['plan_id'] = $insurancePlan->id;
            }

            $result['insurance_id'] = $insurance->id;
        }

        return $result;
    }

    /**
     * Copied from old crawler
     *
     * @param Crawler $crawler
     *
     * @return array
     */
    private function getBillingData(Crawler $crawler)
    {
        $result = [
            'pos'          => null,
            'procedure_id' => null,
        ];
        $billings = $crawler->filter('#ctl00_phFolderContent_ucVisitLineItem_hdnJsLoadBillableLineItem')->getNode(0);
        if (!is_null($billings)) {
            $billings = $billings->getAttribute('value');
            $billings = htmlspecialchars_decode($billings);
            $billings = json_decode($billings, true);
            $billings = data_get($billings, '0.0');
        }
        if (is_null($billings)) {
            return $result;
        }
        $procedure = PatientInsuranceProcedure::query()->updateOrCreate(['code' => $billings['CPT']], [
            'name' => $billings['Description'],
        ]);

        return [
            'pos'          => $billings['PlaceOfService'],
            'procedure_id' => $procedure->getKey(),
        ];
    }

    private function syncPatientVisitDiagnoses(PatientVisit $visit, array $diagnoses): void
    {
        PatientVisitDiagnose::query()
            ->where('visit_id', $visit->id)
            ->whereNotIn('diagnose_id', $diagnoses)
            ->each(function ($patientVisitDiagnose) {
                $patientVisitDiagnose->delete();
            });

        Diagnose::query()
            ->select('id')
            ->whereNotIn('id', $visit->diagnoses()->pluck('diagnoses.id'))
            ->whereIn('id', $diagnoses)
            ->each(function ($diagnose) use ($visit) {
                PatientVisitDiagnose::create([
                    'visit_id' => $visit->id,
                    'diagnose_id' => $diagnose->id,
                ]);
            });
    }
}
