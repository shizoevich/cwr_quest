<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\OnlineVisitsWithoutLogsStatisticsReportHelper;

class GenerateOnlineVisitsWithoutLogsFromPreviousPeriodReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:online-visits-without-logs-from-previous-period';

    protected function getData(string $startDate, string $endDate): array
    {
        return OnlineVisitsWithoutLogsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return OnlineVisitsWithoutLogsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'online_visits_without_logs_from_previous_period.xlsx';
    }
}
