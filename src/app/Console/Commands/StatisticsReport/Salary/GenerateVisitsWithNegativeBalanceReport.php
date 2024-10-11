<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\VisitsWithNegativeBalanceStatisticsReportHelper;

class GenerateVisitsWithNegativeBalanceReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:visits-with-negative-balance';

    protected function getData(string $startDate, string $endDate): array
    {
        return VisitsWithNegativeBalanceStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return VisitsWithNegativeBalanceStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'visits_with_negative_balance.xlsx';
    }
}
