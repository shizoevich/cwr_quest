<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\CompletedInitialSurveyStatisticsReportHelper;

class GenerateCompletedInitialSurveyRecordsReport extends AbstractSalaryStatisticsReport
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:completed-initial-survey-records';

    protected function getData(string $startDate, string $endDate): array
    {
        return CompletedInitialSurveyStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return CompletedInitialSurveyStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'completed_initial_survey_records.xlsx';
    }
}