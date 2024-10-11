<?php

namespace App\Console\Commands\StatisticsReport;

use App\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateInsuranceStatisticsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics-report:insurance-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an Excel report on patient insurance statistics for a date range.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $startDate = Carbon::today()->startOfYear();
        $endDate = Carbon::today()->endOfMonth();

        $this->prepareInsuranceData($startDate, $endDate);
    }

    private function prepareInsuranceData($startDate, $endDate)
    {
        $insurancesData = Appointment::query()
            ->select(['providers.provider_name', 'patient_insurances.insurance'])
            ->selectRaw('count(distinct patients.id) as patients_qty')
            ->join('providers', 'providers.id', 'appointments.providers_id')
            ->join('patients', 'patients.id', 'appointments.patients_id')
            ->join('patient_insurances', 'patient_insurances.id', 'patients.primary_insurance_id')
            ->where('patients.is_test', 0)
            ->where('appointments.time', '>=', $startDate->timestamp)
            ->where('appointments.time', '<=', $endDate->timestamp)
            ->groupBy(['providers.id', 'patient_insurances.id'])
            ->toBase()
            ->get()
            ->groupBy('insurance')
            ->map(function (Collection $items) {
                return $items->mapWithKeys(function ($item) {
                    return [$item->provider_name => $item->patients_qty];
                })->toArray();
            })
            ->toArray();

        if(!empty($insurancesData)){
            $this->generateInsuranceReport($insurancesData);
        } else {
            $this->warn('No data');
        }
    }

    private function generateInsuranceReport($insurancesData)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Therapist / Insurance');

        $column = 'B';
        foreach (array_keys($insurancesData) as $insuranceName) {
            $sheet->setCellValue($column . '1', $insuranceName);
            $column++;
        }

        $allTherapists = [];
        foreach ($insurancesData as $providers) {
            foreach ($providers as $therapist => $count) {
                $allTherapists[$therapist] = true;
            }
        }

        $row = 2;
        foreach (array_keys($allTherapists) as $therapistName) {
            $sheet->setCellValue('A' . $row, $therapistName);

            $column = 'B';
            foreach ($insurancesData as $insuranceName => $providers) {
                $count = $providers[$therapistName] ?? 0;
                $sheet->setCellValue($column . $row, $count);
                $column++;
            }
            $row++;
        }

        foreach (range('A', $column) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $fileName = 'insurances_statistics_2024.xlsx';
        $savePath = storage_path('app/temp/' . $fileName);
        if (\Storage::disk('local')->exists('temp/'. $fileName)) {
            \Storage::disk('local')->delete('temp/'. $fileName);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($savePath);

        $this->info("The file is successfully saved: {$savePath}");
    }
}
