<?php

namespace App\Console\Commands\StatisticsReport;

use App\Helpers\StatisticsReport\ActiveAppointmentsStatisticsReportHelper;
use App\Helpers\StatisticsReport\CancelledAppointmentsStatisticsReportHelper;
use App\Helpers\StatisticsReport\InactiveOrLostPatientsStatisticsReportHelper;
use App\Helpers\StatisticsReport\OnlineVisitsWithCallLogsStatisticsReportHelper;
use App\Helpers\StatisticsReport\OnlineVisitsWithoutLogsStatisticsReportHelper;
use App\Helpers\StatisticsReport\VisitsWithNegativeBalanceStatisticsReportHelper;
use App\Helpers\StatisticsReport\VisitsWithoutCompletedFormsStatisticsReportHelper;
use App\Helpers\StatisticsReport\VisitsWithoutFutureAppointmentsStatisticsReportHelper;
use App\Helpers\StatisticsReport\KaiserReferralsFillingStatisticsReportHelper;
use App\Helpers\StatisticsReport\CompletedInitialSurveyStatisticsReportHelper;
use App\Helpers\StatisticsReport\CompletedSecondSurveyStatisticsReportHelper;
use App\Helpers\StatisticsReport\PatientsWithMissedAppointmentsStatisticsReportHelper;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateSalaryGeneralStatisticsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics-report:salary-general-statistics';

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
        $previousPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);
        $data = $this->prepareSalaryData($previousPeriod->start_date, $previousPeriod->end_date);
        $this->generateExcelReport($data, $previousPeriod->start_date, $previousPeriod->end_date);
    }

    private function prepareSalaryData(string $startDate, string $endDate): array
    {
        return [
            $this->prepareActiveAppointmentsData($startDate, $endDate),
            $this->prepareCancelledAppointmentsData($startDate, $endDate),
            $this->prepareInactiveOrLostPatientsData($startDate, $endDate),
            $this->prepareOnlineVisitsWithCallLogsData($startDate, $endDate),
            $this->prepareOnlineVisitsWithoutLogsData($startDate, $endDate),
            $this->prepareVisitsWithNegativeBalanceData($startDate, $endDate),
            $this->prepareVisitsWithoutCompletedFormsData($startDate, $endDate),
            $this->prepareVisitsWithoutFutureAppointmentsData($startDate, $endDate),
            $this->prepareKaiserReferralsFillingData($startDate, $endDate),
            $this->prepareCompletedInitialSurveyData($startDate, $endDate),
            $this->prepareCompletedSecondSurveyData($startDate, $endDate),
            $this->preparePatientsWithMissedAppointmentsData($startDate, $endDate),
        ];
    }

    private function generateExcelReport(array $data, string $startDate, string $endDate): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($data as $item) {
            $activeWorksheet = $spreadsheet->createSheet();
            $activeWorksheet->setTitle($item['title']);
            $activeWorksheet->fromArray([$item['columnNames']], null, 'A1');
            $activeWorksheet->fromArray($item['data'], null, 'A2');
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "reports_{$startDate}__{$endDate}.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }

    private function prepareActiveAppointmentsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: active appointments');

        return [
            'title' => 'Active Appointments',
            'columnNames' => ActiveAppointmentsStatisticsReportHelper::getColumnNames(),
            'data' => ActiveAppointmentsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareCancelledAppointmentsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: cancelled appointments');

        return [
            'title' => 'Cancelled Appointments',
            'columnNames' => CancelledAppointmentsStatisticsReportHelper::getColumnNames(),
            'data' => CancelledAppointmentsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareInactiveOrLostPatientsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: inactive or lost patients');

        return [
            'title' => 'Inactive or Lost Patients',
            'columnNames' => InactiveOrLostPatientsStatisticsReportHelper::getColumnNames(),
            'data' => InactiveOrLostPatientsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareOnlineVisitsWithCallLogsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: online visits with call logs');

        return [
            'title' => 'Visits With Call Logs',
            'columnNames' => OnlineVisitsWithCallLogsStatisticsReportHelper::getColumnNames(),
            'data' => OnlineVisitsWithCallLogsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareOnlineVisitsWithoutLogsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: online visits without logs');

        return [
            'title' => 'Visits Without Logs',
            'columnNames' => OnlineVisitsWithoutLogsStatisticsReportHelper::getColumnNames(),
            'data' => OnlineVisitsWithoutLogsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareVisitsWithNegativeBalanceData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: visits with negative balance');

        return [
            'title' => 'Visits With Negative Balance',
            'columnNames' => VisitsWithNegativeBalanceStatisticsReportHelper::getColumnNames(),
            'data' => VisitsWithNegativeBalanceStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareVisitsWithoutCompletedFormsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: visits without completed forms');

        return [
            'title' => 'Visits Without Completed Forms',
            'columnNames' => VisitsWithoutCompletedFormsStatisticsReportHelper::getColumnNames(),
            'data' => VisitsWithoutCompletedFormsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareVisitsWithoutFutureAppointmentsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: visits without future appointments');

        return [
            'title' => 'Visits Without Future Appts.',
            'columnNames' => VisitsWithoutFutureAppointmentsStatisticsReportHelper::getColumnNames(),
            'data' => VisitsWithoutFutureAppointmentsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareKaiserReferralsFillingData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: kaiser referrals filling');

        return [
            'title' => 'Kaiser Referrals',
            'columnNames' => KaiserReferralsFillingStatisticsReportHelper::getColumnNames(),
            'data' => KaiserReferralsFillingStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareCompletedInitialSurveyData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: completed initial surveys');

        return [
            'title' => 'Completed Initial Surveys',
            'columnNames' => CompletedInitialSurveyStatisticsReportHelper::getColumnNames(),
            'data' => CompletedInitialSurveyStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function prepareCompletedSecondSurveyData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: completed second surveys');

        return [
            'title' => 'Completed Second Surveys',
            'columnNames' => CompletedSecondSurveyStatisticsReportHelper::getColumnNames(),
            'data' => CompletedSecondSurveyStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }

    private function preparePatientsWithMissedAppointmentsData(string $startDate, string $endDate): array
    {
        $this->info('Prepare data: patients with missed appointments');

        return [
            'title' => 'Weekly Pts. With Missed Appts.',
            'columnNames' => PatientsWithMissedAppointmentsStatisticsReportHelper::getColumnNames(),
            'data' => PatientsWithMissedAppointmentsStatisticsReportHelper::getData($startDate, $endDate),
        ];
    }
}
