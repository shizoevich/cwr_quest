<?php

namespace App\Services\ExcelReport;

use App\Patient;
use App\PatientNote;
use App\Provider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class ProgressNotesExcelReport
{
    private $startDate;
    private $lastDate;
    
    public function __construct($startDate, $lastDate)
    {
        $this->startDate = $startDate;
        $this->lastDate = $lastDate;
    }
    
    public function generateReport()
    {
        $googleDrive = config('google_drive_folder.progress_note_excel_report');
        $patientProgressNoteReportRangeDateFolder = $this->startDate . '___' .$this->lastDate ;
        
        $googleDriveFoldersListToDelete = array_filter(Storage::disk('google')->listContents($googleDrive, false), function ($folder) {
            return $folder['type'] === 'dir';
        });
       
        foreach ($googleDriveFoldersListToDelete as $folder) {
            Storage::disk('google')->deleteDir($folder['basename']);
        }
      
        Storage::disk('google')->createDir("/$googleDrive/$patientProgressNoteReportRangeDateFolder");
      
        $folderNameForReport = null;
        $googleDriveFoldersListOfExcelProgressNoteReport = Storage::disk('google')->listContents("/$googleDrive", false);

        foreach ($googleDriveFoldersListOfExcelProgressNoteReport as $folder) {
            if ($folder['name'] === $patientProgressNoteReportRangeDateFolder) {
                $folderNameForReport = $folder['basename'];
                break; 
            }
        }
        Provider::select('id', 'provider_name')->orderBy('id')->chunk(5, function ($providers)  use ($folderNameForReport)  {
            foreach ($providers as $provider) {
                $data = $this->collectDataForExcelReport($provider);
                $this->generateExcelReport($data, $provider->provider_name, $folderNameForReport);
            }
        });
    }

    private function collectDataForExcelReport($provider)
    {
        $patientNotes = PatientNote::select(
            'patients_id',
            'is_finalized',
            'date_of_service',
            'diagnosis_icd_code',
            'long_range_treatment_goal',
            'shortterm_behavioral_objective',
            'additional_comments',
            'plan',
            'interventions',
            'progress_and_outcome'
        )
            ->where('provider_id', $provider->id)
            ->whereBetween('date_of_service', [$this->startDate, $this->lastDate])
            ->get();
        
        return $patientNotes->groupBy('patients_id')->map(function ($notes) {
            return [
                'patient_id' => $notes[0]['patients_id'],
                'data' => $notes->map(function ($note) {
                    $noteData = collect($note)->except('patients_id')->toArray();
                    $noteData['is_finalized'] = ($note['is_finalized'] == 1) ? 'Yes' : 'No';
                    return $noteData;
                })->toArray(),
            ];
        })->values()->all();
    }

    private function generateExcelReport($patients, $providerFullName, $folderNameForReport)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($patients as $client) {
            $activeWorksheet = $spreadsheet->createSheet();
            $patientFullName = Patient::select('first_name', 'last_name')->find($client['patient_id']);
            $activeWorksheet->setTitle($patientFullName->first_name . ' ' . $patientFullName->last_name);

            $columnNames = [
                'Is Finalized', 'Date of Service', 'Diagnosis and ICD Code',
                'Long Range Treatment Goal', 'Shortterm Behavioral Objective',
                'Additional Comments', 'Plan', 'Interventions', 'Progress and Outcome',
            ];

            $activeWorksheet->fromArray([$columnNames], null, 'A1');

            $data = $client['data'];
            $activeWorksheet->fromArray($data, null, 'A2');

            foreach (range('A', 'I') as $column) {
                $activeWorksheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $filename = $providerFullName . '.xlsx';
        $path = storage_path('app/' . $filename);
        $writer->save($path);
        Storage::disk('google')->put($folderNameForReport  . '/' . $filename, file_get_contents($path));
    }
}
