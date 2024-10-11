<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\ActiveAppointmentsStatisticsReportHelper;

class GenerateActiveAppointmentsFromPreviousPeriodReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:active-appointments-from-previous-period';

    protected function getData(string $startDate, string $endDate): array
    {
        return ActiveAppointmentsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return ActiveAppointmentsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'active_appointments_from_previous_period.xlsx';
    }
}
