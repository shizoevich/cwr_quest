<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\InactiveOrLostPatientsStatisticsReportHelper;

class GenerateInactiveOrLostPatientsFromPreviousPeriodReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:inactive-or-lost-patients-from-previous-period';

    protected function getData(string $startDate, string $endDate): array
    {
        return InactiveOrLostPatientsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return InactiveOrLostPatientsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'inactive_or_lost_patients_from_previous_period.xlsx';
    }
}
