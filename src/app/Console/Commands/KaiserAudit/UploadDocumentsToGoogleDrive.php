<?php

namespace App\Console\Commands\KaiserAudit;

use App\Appointment;
use App\Jobs\Patients\AssessmentForms\Generate;
use App\Models\Patient\PatientElectronicDocument;
use App\Patient;
use App\PatientDocument;
use Bus;
use Carbon\Carbon;
use DB;
use DOMDocument;
use Exception;
use File;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class UploadDocumentsToGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:upload-documents-to-google-drive {patientMrn?} {--f|file= : Path to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $auditGoogleDriveFolder;

    private $auditGoogleDriveFolderMeta;

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
        $this->initGoogleDriveFolderForAudit();
        $this->configureDocumentCreator();

        $appointments = $this->appointments();

        $bar = $this->output->createProgressBar(count($appointments));

        foreach ($appointments as $appointment) {
            try {
                $this->processAppointment($appointment);
            } catch (Exception $exception) {
                $this->warn("Appointment $appointment->id: {$exception->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
    }

    private function processAppointment(Appointment $appointment)
    {
        $patientFolder = $this->findOrCreatePatientFolder($appointment->patient);

        if ($appointment->is_initial) {
            if ($appointment->initial_assessment_type === PatientElectronicDocument::class) {
                $doc = PatientElectronicDocument::find($appointment->initial_assessment_id);

                if ($doc && Storage::disk('patient_assessment_forms')->exists($appointment->initial_assessment_id . '.docx')) {
                    $this->saveFileToPatientFolder(
                        $patientFolder['basename'],
                        "{$appointment->appt_date}_InitialAssessment_{$appointment->patient->first_name}_{$appointment->patient->last_name}.docx",
                        Storage::disk('patient_assessment_forms')->get($appointment->initial_assessment_id . '.docx'),
                        $doc,
                    );
                }
            } else if ($appointment->initial_assessment_type === PatientDocument::class) {
                $doc = PatientDocument::find($appointment->initial_assessment_id);

                if ($doc && Storage::disk('patients_docs')->exists($doc->aws_document_name)) {
                    $extension = File::extension($doc->aws_document_name);

                    $this->saveFileToPatientFolder(
                        $patientFolder['basename'],
                        "{$appointment->appt_date}_InitialAssessment_{$appointment->patient->first_name}_{$appointment->patient->last_name}.{$extension}",
                        Storage::disk('patients_docs')->get($doc->aws_document_name),
                        $doc,
                    );
                }
            }
        } else if (optional($appointment->patientNote)->is_finalized && Storage::disk('progress_notes')->exists("{$appointment->patientNote->id}.pdf")) {
            $this->saveFileToPatientFolder(
                $patientFolder['basename'],
                "{$appointment->appt_date}_ProgressNote_{$appointment->patient->first_name}_{$appointment->patient->last_name}.pdf",
                Storage::disk('progress_notes')->get("{$appointment->patientNote->id}.pdf")
            );
        }
    }

    /**
     * @return Appointment[] array
     */
    private function appointments(): array
    {
        $appointments = [];

        foreach ($this->parseFile() as $item) {
            $appointment = Appointment::query()
                ->select('appointments.*')
                ->selectRaw("date(from_unixtime(`appointments`.`time`)) AS appt_date")
                ->where('appointments.patients_id', $item[4])
                ->where('appointments.appointment_statuses_id', 1)
                ->whereRaw("date(from_unixtime(appointments.time)) = '{$item[3]}'")
                ->when($this->argument('patientMrn'), function (Builder $query, $mrn) {
                    return $query
                        ->join('patients', 'appointments.patients_id', 'patients.id')
                        ->where('patients.subscriber_id', $mrn);
                })
                ->with('patient', 'patientNote')
                ->first();
            if ($appointment) {
                $appointments[] = $appointment;
            }
        }

        return $appointments;
    }

    private function parseFile(): Generator
    {
        $filePath = $this->option('file') ?? storage_path('app/temp/25_patients.csv');

        if (($open = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($open, 1000, ";")) !== false) {
                yield $data;
            }

            fclose($open);
        }
    }

    private function initGoogleDriveFolderForAudit()
    {
        $this->auditGoogleDriveFolderMeta = $this->findOrCreateGoogleDriveFolderForAudit();
        $this->auditGoogleDriveFolder = Storage::disk('google')
            ->listContents($this->auditGoogleDriveFolderMeta['basename']);
    }

    private function findOrCreateGoogleDriveFolderForAudit(): array
    {
        $folder = array_first(Storage::disk('google')->listContents(), function ($file) {
            return $file['name'] === 'Kaiser Audit' && $file['type'] === 'dir';
        });

        if (!$folder) {
            Storage::disk('google')->createDir('Kaiser Audit');

            $folder = array_first(Storage::disk('google')->listContents(), function ($file) {
                return $file['name'] === 'Kaiser Audit' && $file['type'] === 'dir';
            });
        }

        return $folder;
    }

    private function findOrCreatePatientFolder(Patient $patient): array
    {
        $patientFolder = array_first($this->auditGoogleDriveFolder, function ($file) use ($patient) {
            return $file['name'] === $patient->subscriber_id && $file['type'] === 'dir';
        });

        if (!$patientFolder) {
            Storage::disk('google')
                ->createDir($this->auditGoogleDriveFolderMeta['basename'] . '/' . $patient->subscriber_id);

            $this->auditGoogleDriveFolder = Storage::disk('google')
                ->listContents($this->auditGoogleDriveFolderMeta['basename']);

            $patientFolder = array_first($this->auditGoogleDriveFolder, function ($file) use ($patient) {
                return $file['name'] === $patient->subscriber_id && $file['type'] === 'dir';
            });
        }

        return $patientFolder;
    }

    private function saveFileToPatientFolder(string $folder, string $fileName, string $content, $document = null)
    {
        [$fileName, $content] = $this->processDocument($fileName, $content, $document);

        $patientDocs = Storage::disk('google')->listContents($folder);

        $doc = array_first($patientDocs, function ($file) use ($fileName) {
            return $file['name'] === $fileName && $file['type'] === 'file';
        });

        if ($doc) {
            Storage::disk('google')->delete($doc['basename']);
        }

        Storage::disk('google')
            ->put("$folder/$fileName", $content);
    }

    private function processDocument(string $fileName, string $content, $document): array
    {
        $extension = File::extension($fileName);

        if ($extension === 'pdf') {
            return [$fileName, $this->updatePdfMeta($content)];
        }

        if ($extension === 'docx') {
            return [File::name($fileName) . '.pdf', $this->convertDocxToPdf($content, $document)];
        }

        return [$fileName, $content];
    }

    private function updatePdfMeta(string $content): string
    {
        $date = Carbon::now();

        // PatientDocument
        $dateMeta = '/CreationDate (D:' . $date->copy()->tz('utc')->format('YmdHis') . 'Z)';
        $content = preg_replace('/(\/CreationDate \(D:)\d{14}(Z\))/i', $dateMeta, $content);

        // PatientNote
        $dateFields = ['CreateDate', 'ModifyDate', 'MetadataDate'];
        foreach ($dateFields as $dateField) {
            $dateMeta = "<xmp:$dateField>" . $date->toIso8601String() . "</xmp:$dateField>";
            $content = preg_replace("/(<xmp:$dateField>[0-9-+:TZ]+<\\/xmp:$dateField>)/i", $dateMeta, $content);
        }

        return $content;
    }

    private function convertDocxToPdf(string $content, $document): string
    {
        $tmpDocPath = "temp/upload-documents-to-google-drive/$document->id.docx";
        $tmpPdfPath = "temp/upload-documents-to-google-drive/$document->id.pdf";

        if ($document instanceof PatientElectronicDocument && $document->type->password) {
            Bus::dispatchNow(new Generate($document));
        } else {
            Storage::disk('local')->put($tmpDocPath, $content);
        }

        $command = 'unoconv -f pdf ' . Storage::disk('local')->path($tmpDocPath) . ' ' . Storage::disk('local')->path($tmpPdfPath) . ' > /dev/null 2>&1';

        exec($command);

        Storage::disk('local')->delete($tmpDocPath);

        if (!Storage::disk('local')->exists($tmpPdfPath)) {
            throw new Exception("PDF file for document was not created");
        }

        $content = Storage::disk('local')->get($tmpPdfPath);

        Storage::disk('local')->delete($tmpPdfPath);

        return $content;
    }

    // @todo remove if unused
    // private function updateDocxMeta(string $content): string
    // {
    //     $date = Carbon::now();

    //     $tmpFileName = Str::random();
    //     $tmpDocPath = "temp/upload-documents-to-google-drive/$tmpFileName.docx";
    //     $modifiedTmpDocPath = "temp/upload-documents-to-google-drive/modified-$tmpFileName.docx";
    //     $tmpExtractedPath = "temp/upload-documents-to-google-drive/extracted-$tmpFileName";

    //     Storage::disk('local')->put($tmpDocPath, $content);
    //     $zip = new ZipArchive();
    //     if ($zip->open(Storage::disk('local')->path($tmpDocPath))) {
    //         Storage::disk('local')->makeDirectory($tmpExtractedPath);
    //         $zip->extractTo(Storage::disk('local')->path($tmpExtractedPath));
    //         $zip->close();

    //         $coreXmlPath = $tmpExtractedPath . '/docProps/core.xml';
    //         $appXmlPath = $tmpExtractedPath . '/docProps/app.xml';

    //         $xml = new DOMDocument();
    //         $xml->load(Storage::disk('local')->path($coreXmlPath));
    //         $this->setValueToXml($xml, 'revision', 1);
    //         $this->setValueToXml($xml, 'created', $date->toIso8601String());
    //         $this->setValueToXml($xml, 'modified', $date->toIso8601String());
    //         $this->setValueToXml($xml, 'lastPrinted', '');
    //         $this->setValueToXml($xml, 'creator', '');
    //         $xml->save(Storage::disk('local')->path($coreXmlPath));

    //         $xml->load(Storage::disk('local')->path($appXmlPath));
    //         $this->setValueToXml($xml, 'TotalTime', 0);
    //         $xml->save(Storage::disk('local')->path($appXmlPath));

    //         if ($zip->open(Storage::disk('local')->path($modifiedTmpDocPath), ZipArchive::CREATE)) {
    //             $files = new RecursiveIteratorIterator(
    //                 new RecursiveDirectoryIterator(Storage::disk('local')->path($tmpExtractedPath)),
    //                 RecursiveIteratorIterator::LEAVES_ONLY
    //             );

    //             foreach ($files as $file) {
    //                 if (!$file->isDir()) {
    //                     $filePath = $file->getRealPath();
    //                     $relativePath = substr($filePath, strlen(Storage::disk('local')->path($tmpExtractedPath)) + 1);
    //                     $zip->addFile($filePath, $relativePath);
    //                 }
    //             }

    //             $zip->close();

    //             $content = Storage::disk('local')->get($modifiedTmpDocPath);
    //         }
    //     }

    //     Storage::disk('local')->delete([
    //         $tmpDocPath,
    //         $tmpExtractedPath,
    //         $modifiedTmpDocPath,
    //     ]);

    //     return $content;
    // }

    // private function setValueToXml(DOMDocument $xml, string $tag, string $value)
    // {
    //     $tags = $xml->getElementsByTagName($tag);

    //     if ($tags->count()) {
    //         $tags->item(0)->nodeValue = $value;
    //     }
    // }

    private function configureDocumentCreator()
    {
        $configPath = dirname(config('assessment_form_generator.config_path')) . '/upload-documents-to-google-drive.config.ini';

        config(['assessment_form_generator.config_path' => $configPath]);
        config(['assessment_form_generator.mode' => 'local']);
    }
}
