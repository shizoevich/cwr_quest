<?php

namespace App\Console\Commands\KaiserAudit;

use App\PatientNote;
use Illuminate\Console\Command;
use Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use TonchikTm\PdfToHtml\Pdf;

class CheckSignatureInProgressNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:check-signature-in-progress-notes {filePath?} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $tesseractOcr;

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
        $this->tesseractOcr = new TesseractOCR();

        foreach ($this->parseFile() as $item) {
            $progressNote = PatientNote::select(['patient_notes.*'])
                ->join('appointments', 'appointments.id', 'patient_notes.appointment_id')
                ->where('appointments.patients_id', $item[4])
                ->where('appointments.appointment_statuses_id', 1)
                ->whereRaw("date(from_unixtime(appointments.time)) = '{$item[3]}'")
                ->where('patient_notes.is_finalized', '=', true)
                ->first();

            if ($progressNote && Storage::disk('progress_notes')->exists("$progressNote->id.pdf")) {
                if (!Storage::disk('local')->exists("temp/check-therapist-signature/$progressNote->id-1.png") || $this->option('force')) {
                    Storage::disk('local')->put(
                        "temp/check-therapist-signature/$progressNote->id.pdf",
                        Storage::disk('progress_notes')->get("$progressNote->id.pdf")
                    );

                    $pdfPath = Storage::disk('local')->path("temp/check-therapist-signature/$progressNote->id.pdf");
                    $imageTemplate = Storage::disk('local')->path("temp/check-therapist-signature/$progressNote->id-%d.png");

                    // http://www.ghostscript.com/ REQUIRED
                    exec("gs -dNOPAUSE -sDEVICE=png16m -r300 -sOutputFile={$imageTemplate} -q {$pdfPath} -c quit");
                }

                if (!$this->isPdfHasSignature($progressNote)) {
                    $this->warn("Signature not found in $progressNote->id document.");
                }
            }
        }
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

    private function findSignatureIndex(array $array)
    {
        $arrayReversed = array_reverse($array, true);

        foreach ($arrayReversed as $index => $string) {
            if (str_starts_with(strtolower($string), 'signature')) {
                return $index;
            }
        }

        return -1;
    }

    private function isTextHasSignature(PatientNote $patientNote, string $text): bool
    {
        $array = array_values(array_filter(explode("\n", $text)));

        $signatureIndex = $this->findSignatureIndex($array);

        if (count($array) - 1 > $signatureIndex) {
            return true;
        }

        if (preg_match('/Signature:(.+)/s', $array[$signatureIndex])) {
            return true;
        }

        // The actual signature may appear above the "Signature" field https://monosnap.com/file/36aEp80Fx8Wmv3zpZC8bOlGPvVfO6S
        if (!str_ends_with($patientNote->plan, $array[$signatureIndex - 1])) {
            return true;
        }

        return false;
    }

    private function isPdfHasSignature(PatientNote $patientNote): bool
    {
        $pdfPath = Storage::disk('local')->path("temp/check-therapist-signature/$patientNote->id.pdf");
        // for local usage on Windows
        // $pdf = new Pdf($pdfPath, [
        //     'pdftohtml_path' => 'D:\programs\poppler-0.89.0\bin\pdftohtml',
        //     'pdfinfo_path' => 'D:\programs\poppler-0.89.0\bin\pdfinfo',
        // ]);
        $pdf = new Pdf($pdfPath, [
            'pdftohtml_path' => '/usr/bin/pdftohtml',
            'pdfinfo_path' => '/usr/bin/pdfinfo',
        ]);
        $totalPages = (int)$pdf->countPages();

        $text = '';

        for ($page = 1; $page <= $totalPages; $page++) {
            $image = Storage::disk('local')->path("temp/check-therapist-signature/$patientNote->id-$page.png");

            $textFromImage = $this->tesseractOcr->image($image)->run();

            $text .= "\n$textFromImage";
        }

        return $this->isTextHasSignature($patientNote, $text);
    }
}
