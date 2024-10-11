<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\VisitsWithoutFutureAppointmentsStatisticsReportHelper;

class GenerateVisitsWithoutFutureAppointmentsReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:visits-without-future-appointments';

    protected function getData(string $startDate, string $endDate): array
    {
        return VisitsWithoutFutureAppointmentsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return VisitsWithoutFutureAppointmentsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'visits_without_future_appointments.xlsx';
    }
}
