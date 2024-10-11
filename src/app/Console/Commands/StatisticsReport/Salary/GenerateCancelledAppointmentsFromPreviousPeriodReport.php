<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\CancelledAppointmentsStatisticsReportHelper;

class GenerateCancelledAppointmentsFromPreviousPeriodReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:cancelled-appointments-from-previous-period';

    protected function getData(string $startDate, string $endDate): array
    {
        return CancelledAppointmentsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return CancelledAppointmentsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'cancelled_appointments_from_previous_period.xlsx';
    }
}
