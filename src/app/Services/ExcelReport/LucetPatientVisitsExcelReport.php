<?php

namespace App\Services\ExcelReport;

use App\Patient;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;

class LucetPatientVisitsExcelReport
{
    private $subscriberId;
    
    public function __construct($subscriberId)
    {
        $this->subscriberId = $subscriberId;
    }
    
    public function generateReport()
    {
        $data = $this->collectDataForExcelReport();
        $this->generateExcelReport($data);
    }

    public function collectDataForExcelReport()
    {
        return Patient::select(['patient_visits.date', 'appointments.is_initial', 'appointments.initial_assessment_id', 'patient_notes.*'])
            ->where('patients.subscriber_id', $this->subscriberId)
            ->join('patient_visits', function($join) {
                $join->on($join->table . '.patient_id', '=', 'patients.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->leftJoin('appointments', 'appointments.id', '=', 'patient_visits.appointment_id')
            ->leftJoin('patient_notes', function($join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->where($join->table . '.is_finalized', 1)
                    ->whereNull($join->table . '.deleted_at');
            })
            ->get()
            ->map(function ($visit) {
                return [
                    'Visit Date' => $visit->date,
                    'Is Initial Evaluation' => $visit->is_initial ? 'Yes' : 'No',
                    'Has Initial Assessment' => isset($visit->initial_assessment_id) ? 'Yes' : 'No',
                    'Has Progress Note' => isset($visit->id) ? 'Yes' : 'No',
                    'Treatment Modality' => $visit->treatment_modality ?? '-',
                    'Diagnosis and ICD Code' => $visit->diagnosis_icd_code ?? '-',
                    'Long Range Treatment Goal' => $visit->long_range_treatment_goal ?? '-',
                    'Shortterm Behavioral Objective' => $visit->shortterm_behavioral_objective ?? '-',
                    'Additional Comments' => $visit->additional_comments ?? '-',
                    'Plan' => $visit->plan ?? '-',
                    'Interventions' => $visit->interventions ?? '-',
                    'Progress and Outcome' => $visit->progress_and_outcome ?? '-',
                ];
            });
    }

    private function generateExcelReport($visitsData)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Visit Date',
            'Is Initial Evaluation',
            'Has Initial Assessment',
            'Has Progress Note',
            'Treatment Modality',
            'Diagnosis and ICD Code',
            'Long Range Treatment Goal',
            'Shortterm Behavioral Objective',
            'Additional Comments',
            'Plan',
            'Interventions',
            'Progress and Outcome',
        ];

        $boldFontStyle = [
            'font' => [
                'bold' => true,
            ],
        ];
        $worksheet->getStyle('A1:L1')->applyFromArray($boldFontStyle);

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($visitsData->toArray(), NULL, 'A2');

        foreach (range('A', 'L') as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => StyleBorder::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $worksheet->getStyle('A1:L' . (count($visitsData) + 1))->applyFromArray($borderStyle);

        $writer = new Xlsx($spreadsheet);
        $filename = "Patient_Visits_{$this->subscriberId}.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }
}
