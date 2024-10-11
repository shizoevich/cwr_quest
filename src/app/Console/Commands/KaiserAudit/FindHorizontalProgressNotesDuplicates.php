<?php

namespace App\Console\Commands\KaiserAudit;

use App\Appointment;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;

class FindHorizontalProgressNotesDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:find-horizontal-progress-notes-duplicates';

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
        $filename = "25_patients.csv";
        $path = storage_path('app/temp/' . $filename);
        $array = [];

        if (($open = fopen($path, "r")) !== false) {
            while (($data = fgetcsv($open, 1000, ";")) !== false) {
                $array[] = $data;
            }
         
            fclose($open);
        }

        $patients = collect($array)->groupBy(function ($item) {
            return $item[4];
        });

        $data = [];
        foreach ($patients as $patientVisits) {
            $patientId = $patientVisits[0][4];
            $visitDates = $patientVisits->map(function ($item) {
                return $item[3];
            });

            $progressNotes = Appointment::select(['patient_notes.*', 'patient_notes.date_of_service AS date'])
                ->where('appointments.patients_id', $patientId)
                ->where('appointments.appointment_statuses_id', 1)
                ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) IN ('" . $visitDates->implode("', '") ."')")
                ->join('patient_notes', function($join) {
                    $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                        ->where($join->table . '.is_finalized', 1)
                        ->whereNull($join->table . '.deleted_at');
                })
                ->orderBy('patient_notes.date_of_service')
                ->get();

            foreach ($progressNotes as $progressNote) {
                $interventions = $this->prepareText($progressNote->interventions);
                $progressAndOutcome = $this->prepareText($progressNote->progress_and_outcome);
                if ($this->compareTexts($interventions, $progressAndOutcome)) {
                    $data[] = [
                        'Sample Selection #' => $patientVisits[0][0],
                        'MRN' => $patientVisits[0][1],
                        'Patient ID' => $patientVisits[0][4],
                        'Patient Name' => $progressNote->first_name . ' ' . $progressNote->last_name,
                        'Provider Name' => $progressNote->provider_name ?? '',
                        'Service Date' => $progressNote->date,
                        'Interventions' => $progressNote->interventions ?? '-',
                        'Progress and Outcome' => $progressNote->progress_and_outcome ?? '-',
                    ];
                }
            }
        }

        $this->generateExcelReport($data);
    }

    private function prepareText($text)
    {
        $text = strtolower($text);
        $text = preg_replace("/[^ \w]+/", '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function compareTexts($firstText, $secondText)
    {
        // return str_contains($firstText, $secondText) || str_contains($secondText, $firstText);
        return $firstText == $secondText;
    }

    private function generateExcelReport($visitsData)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Sample Selection #' ,
            'MRN',
            'Patient ID',
            'Patient Name',
            'Provider Name',
            'Service Date',
            'Interventions',
            'Progress and Outcome',
        ];

        // $boldFontStyle = [
        //     'font' => [
        //         'bold' => true,
        //     ],
        // ];
        // $worksheet->getStyle('A1:L1')->applyFromArray($boldFontStyle);

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($visitsData, NULL, 'A2');

        // foreach (range('A', 'L') as $column) {
        //     $worksheet->getColumnDimension($column)->setAutoSize(true);
        // }

        // $borderStyle = [
        //     'borders' => [
        //         'allBorders' => [
        //             'borderStyle' => StyleBorder::BORDER_THIN,
        //             'color' => ['rgb' => '000000'],
        //         ],
        //     ],
        // ];
        // $worksheet->getStyle('A1:L' . (count($visitsData) + 1))->applyFromArray($borderStyle);

        $writer = new Xlsx($spreadsheet);
        $filename = "horizontal_duplicated_notes.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }
}
