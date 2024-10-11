<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\CompletedSecondSurveyStatisticsReportHelper;

class GenerateCompletedSecondSurveyRecordsReport extends AbstractSalaryStatisticsReport
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:completed-second-survey-records';

    protected function getData(string $startDate, string $endDate): array
    {
        return CompletedSecondSurveyStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return CompletedSecondSurveyStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'completed_second_survey_records.xlsx';
    }
}
