<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\VisitsWithoutCompletedFormsStatisticsReportHelper;

class GenerateVisitsWithoutCompletedFormsReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:visits-without-completed-forms';

    protected function getData(string $startDate, string $endDate): array
    {
        return VisitsWithoutCompletedFormsStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return VisitsWithoutCompletedFormsStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'visits_without_completed_forms.xlsx';
    }
}
