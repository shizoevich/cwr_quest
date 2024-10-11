<?php

namespace App\Jobs\Parsers\Guzzle;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Appointment;
use App\Office;
use App\Option;
use App\Models\AppointmentReminderStatus;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use App\Traits\Parsers\OfficeAlly\TableProcessing;

/**
 * Class AppointmentReminderStatusesParser
 * @package App\Jobs\Parsers\Guzzle
 */
class AppointmentReminderStatusesParser extends AbstractParser
{
    use TableProcessing;

    /** @var Carbon|null */
    private $startDate;
    
    /** @var Carbon|null */
    private $endDate;

    /** @var string|null */
    private $account;

    private $columnsMappingTemplate = [
        'appointment_id' => ['index' => null, 'name' => 'Appointment ID', 'required' => true],
        'reminder_status' => ['index' => null, 'name' => 'Reminder Status', 'required' => true],
    ];
    
    /**
     * Create a new job instance.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param string|null $account
     */
    public function __construct(Carbon $startDate = null, Carbon $endDate = null, string $account = Option::OA_ACCOUNT_3)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->account = $account;

        parent::__construct();
    }

    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)($this->account);
        $offices = Office::query()->whereNotNull('external_id')->get();
        $period = $this->getPeriod();

        foreach ($offices as $office) {
            foreach ($period as $date) {
                $appointments = $officeAllyHelper->getAppointments($date, $office->external_id);
                $this->appointmentsCrawler($appointments);
            }
        }
    }
    
    /**
     * @param $appointments
     */
    private function appointmentsCrawler($appointments)
    {
        $crawler = new Crawler($appointments);

        $tableHeaders = $crawler->filter('#divDaily .tblAppts thead > tr > th');
        $columnsMapping = $this->getColumnsMappingWithIndexes($this->columnsMappingTemplate, $tableHeaders);
        $missedColumns = $this->getMissedRequiredColumns($columnsMapping);

        if (count($missedColumns)) {
            $columns = array_column(array_values($missedColumns), 'name');
            $message = '[AppointmentReminderStatusesParser] The following required columns are missed: ' . implode(', ', $columns);
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($message), ['office_ally' => 'emergency']);
            
            return false;
        }

        $crawler->filter('#divDaily .tblAppts > tr')->each(function ($node, $i) use (&$columnsMapping) {
            $appointmentId = $this->getIntVal('appointment_id', $node, $columnsMapping);
            if (empty($appointmentId)) {
                return;
            }

            $reminderStatus = $this->getStringVal('reminder_status', $node, $columnsMapping);
            $reminderStatusId = null;
            if (strlen($reminderStatus)) {
                $temp = AppointmentReminderStatus::firstOrCreate([
                    'status' => $reminderStatus
                ]);
                $reminderStatusId = $temp->id;
            }
            
            $appointmentData = [
                'appointment_reminder_statuses_id' => $reminderStatusId,
            ];
            
            $appointment = Appointment::where('idAppointments', intval($appointmentId))->first();
            if ($appointment) {
                $appointment->update($appointmentData);
            }
        });
    }

    /**
     * @return CarbonPeriod
     */
    private function getPeriod()
    {
        $periodStart = null;
        $periodEnd = null;
        if (isset($this->startDate) && isset($this->endDate)) {
            $periodStart = $this->startDate;
            $periodEnd = $this->endDate;
        } else if (isset($this->startDate) || isset($this->endDate)) {
            $temp = $this->startDate ?? $this->endDate;
            $periodStart = $temp;
            $periodEnd = $temp;
        } else {
            $periodStart = Carbon::today()->subDays(config('parser.parsing_depth'));
            $periodEnd = Carbon::today()->addDays(config('parser.parsing_depth_after_today'));
        }
        
        return CarbonPeriod::create($periodStart, $periodEnd);
    }
}
