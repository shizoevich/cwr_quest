<?php

namespace App\Console\Commands\GoogleDrive;

use App\Models\Patient\PatientForm;
use App\Traits\GoogleDrive\CopyPatientDocumentService;
use Illuminate\Console\Command;

class CopyPatientForms extends Command
{
    use CopyPatientDocumentService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:patient_forms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy patient forms from s3 to google drive';

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
        PatientForm::query()
            ->select('id', 'patient_id', 'data', 'google_drive', 'created_at')
            ->where('google_drive', '=', false)
            ->chunk(50, function ($patientForms) {
                foreach ($patientForms as $patientForm) {
                    if (isset($patientForm->data['document_type_id']) === true) {
                        $this->makeCopyPatientDocument(
                            $patientForm->data['original_document_name'],
                            $patientForm->data['document_type_id'],
                            $patientForm->patient_id,
                            $patientForm->created_at,
                            $patientForm->data['aws_document_name']
                        );
                        PatientForm::where('id', $patientForm->id)->update(['google_drive' => true]);
                    } elseif (isset($patientForm->data['document_type_id']) === false) {
                        for ($i = 0; $i < count($patientForm->data); $i++) {
                            if (isset($patientForm->data[$i]['document_type_id']) === true) {
                                $this->makeCopyPatientDocument(
                                    $patientForm->data[$i]['original_document_name'],
                                    $patientForm->data[$i]['document_type_id'],
                                    $patientForm->patient_id,
                                    $patientForm->created_at,
                                    $patientForm->data[$i]['aws_document_name']
                                );
                            }
                        }
                        PatientForm::where('id', $patientForm->id)->update(['google_drive' => true]);
                    }
                }
            });
    }
}
