<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class AbstractSalaryStatisticsReport extends Command
{
    public function handle()
    {
        $previousPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);
        $data = $this->getData($previousPeriod->start_date, $previousPeriod->end_date);
        $this->generateExcelReport($data);
    }

    protected abstract function getData(string $startDate, string $endDate): array;

    protected abstract function getColumnNames(): array;

    protected abstract function getFilename(): string;

    protected function generateExcelReport(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = $this->getColumnNames();

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($data, NULL, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = $this->getFilename();
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }
}