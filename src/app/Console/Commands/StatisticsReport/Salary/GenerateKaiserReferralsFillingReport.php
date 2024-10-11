<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Helpers\StatisticsReport\KaiserReferralsFillingStatisticsReportHelper;

class GenerateKaiserReferralsFillingReport extends AbstractSalaryStatisticsReport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:kaiser-referrals-filling';

    protected function getData(string $startDate, string $endDate): array
    {
        return KaiserReferralsFillingStatisticsReportHelper::getData($startDate, $endDate);
    }

    protected function getColumnNames(): array
    {
        return KaiserReferralsFillingStatisticsReportHelper::getColumnNames();
    }

    protected function getFilename(): string
    {
        return 'kaiser_referrals_filling.xlsx';
    }
}
