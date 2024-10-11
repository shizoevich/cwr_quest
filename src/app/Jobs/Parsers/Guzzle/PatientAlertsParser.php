<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\PatientAlertDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Patients\SyncPatientAlert;
use App\Option;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class PatientAlertsParser
 * @package App\Jobs\Parsers\Guzzle
 */
class PatientAlertsParser extends AbstractParser
{
    /**
     * @var array
     */
    private $patientIds;

    /**
     * PatientAlertsParser constructor.
     *
     * @param array $patientIds
     */
    public function __construct(array $patientIds = [])
    {
        $this->patientIds = $patientIds;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_1);
        Patient::select(['id', 'patient_id'])
            ->where('patient_id', '!=', 11111111)
            ->when($this->patientIds, function ($query) {
                $query->whereIn('patient_id', $this->patientIds);
            })
            ->notArchived()
            ->chunkById(1000, function (Collection $patients) use ($officeAllyHelper) {
                $patients->each(function ($patient) use ($officeAllyHelper) {
                    $alerts = $officeAllyHelper->getPatientAlerts($patient->patient_id);
                    if (!$alerts) {
                        return;
                    }
                    foreach ($alerts as $item) {
                        $alertDTO = new PatientAlertDTO([
                            'officeally_alert_id' => intval($item['cell'][0]),
                            'patient_id' => $patient->id,
                            'date_created' => Carbon::parse($item['cell'][3])->toDateString(),
                            'message' => $item['cell'][4],
                            'resolved_by' => $item['cell'][5],
                            'status' => (string) $item['cell'][8],
                        ]);

                        if (!empty($item['cell'][6])) {
                            $alertDTO->date_resolved = Carbon::parse($item['cell'][6]);
                        }

                        \Bus::dispatchNow(new SyncPatientAlert($alertDTO));
                    }
                });
            });
    }
}
