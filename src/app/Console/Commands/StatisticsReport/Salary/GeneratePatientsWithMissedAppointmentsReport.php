<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\PatientsWithMissedAppointmentsStatisticsReportHelper;

class GeneratePatientsWithMissedAppointmentsReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:patients-with-missed-appointments';

    protected function getData(string $startDate, string $endDate): array
    {
        return PatientsWithMissedAppointmentsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return PatientsWithMissedAppointmentsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'patients_with_missed_appointments.xlsx';
    }
}
