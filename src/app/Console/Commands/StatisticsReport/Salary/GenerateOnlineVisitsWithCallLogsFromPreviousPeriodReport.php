<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\OnlineVisitsWithCallLogsStatisticsReportHelper;

class GenerateOnlineVisitsWithCallLogsFromPreviousPeriodReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:online-visits-with-call-logs-from-previous-period';

    protected function getData(string $startDate, string $endDate): array
    {
        return OnlineVisitsWithCallLogsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return OnlineVisitsWithCallLogsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'online_visits_with_call_logs_from_previous_period.xlsx';
    }
}
