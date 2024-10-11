<?php

namespace App\Console\Commands\KaiserAudit;

use App\Console\Commands\OfficeAllyHelperTrait;
use App\Patient;
use App\PatientVisit;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParseCptCodeFromOfficeAlly extends Command
{
    use OfficeAllyHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:parse-cpt-code-from-office-ally {filePath?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $officeAllyVisits = [];

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
        $this->initOfficeAllyHelpers();

        $result = [];

        $data = $this->parseFile();

        $bar = $this->output->createProgressBar(count($data));

        foreach ($data as $item) {
            $visit = PatientVisit::query()
                ->where('patient_id', $item[4])
                ->where('date', $item[3])
                ->whereNotNull('visit_id')
                ->first();

            $cptCode = null;

            if ($visit) {
                $patient = Patient::find($item[4]);

                $officeAllyVisit = array_first($this->getVisitsFromOfficeAlly($patient), function ($officeAllyVisit) use ($visit) {
                    return $visit->visit_id === $officeAllyVisit['id'];
                });

                if ($officeAllyVisit) {
                    $claim = str_replace_last('|', '', $officeAllyVisit['cell'][16]);

                    $cptCode = $this->officeAllyHelper()->getCptCodeFromClaim($claim);
                }
            }

            $item[] = $cptCode;
            $result[] = $item;

            $bar->advance();
        }

        $bar->finish();

        $this->generateExcelReport($result);
    }

    private function getVisitsFromOfficeAlly(Patient $patient): array
    {
        if (!array_has($this->officeAllyVisits, $patient->patient_id)) {
            $this->officeAllyVisits[$patient->patient_id] = $this->officeAllyHelper()
                ->getVisitListByPatientId($patient->patient_id);
        }

        return $this->officeAllyVisits[$patient->patient_id];
    }

    private function generateExcelReport($visitsData)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Sample Selection #' ,
            'MRN',
            'Claim #',
            'Service Date',
            'Patient ID',
            'CPT Code',
        ];

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($visitsData, NULL, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = "25_patients_with_cpt_code.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }

    private function parseFile(): array
    {
        $filePath = $this->argument('filePath') ?? storage_path('app/temp/25_patients.csv');

        $data = [];

        if (($open = fopen($filePath, "r")) !== false) {
            while (($row = fgetcsv($open, 1000, ";")) !== false) {
                $data[] = $row;
            }

            fclose($open);
        }

        return $data;
    }
}
