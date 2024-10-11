<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientInsurance;
use App\AssessmentForm;
use App\Jobs\Patients\AssessmentForms\Generate;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;
use Carbon\Carbon;

class ResaveDischargeSummaryDocuments extends Command
{
    use CopyPatientNoteAndAssessmentService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resave-documents:discharge-summaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $insurance = PatientInsurance::where('insurance', 'PGBA TriWest VA CCN')->first();
        if (empty($insurance)) {
            return;
        }

        $dischargeSummary = AssessmentForm::where('document_name', 'CWR Patient Discharge Summary')->first();
        if (empty($dischargeSummary)) {
            return;
        }

        PatientElectronicDocument::query()
            ->select(['patient_electronic_documents.*', 'patients.date_of_birth'])
            ->join('patients', 'patients.id', '=', 'patient_electronic_documents.patient_id')
            ->where('patient_electronic_documents.document_type_id', '=', $dischargeSummary->id)
            ->where('patient_electronic_documents.document_version', '=', 1.0)
            ->where('patients.primary_insurance_id', '=', $insurance->id)
            ->chunkById(100, function ($patientDocuments) {
                foreach ($patientDocuments as $patientDocument) {
                    $strToDump = 'Patient discharge summary id: ' . $patientDocument->id . ';';
                    dump($strToDump);

                    $data = json_decode($patientDocument->document_data, true);
                    $dateOfBirth = Carbon::parse($patientDocument->date_of_birth)->format('m/d/Y');
                    $data['date_of_birth'] = $dateOfBirth;

                    PatientElectronicDocument::where('id', $patientDocument->id)->update(['document_data' => json_encode($data)]);

                    $password = $patientDocument->type->password;
                    \Bus::dispatchNow(new Generate($patientDocument, $password));

                    if ($patientDocument->google_drive) {
                        if (($patientDocument->id !== null) &&
                            ($patientDocument->document_type_id !== null) &&
                            ($patientDocument->patient_id !== null) &&
                            ($patientDocument->created_at !== null)
                        ) {
                            $this->makeCopyPatientNoteAndAssessment(
                                $patientDocument->id,
                                $patientDocument->document_type_id,
                                $patientDocument->patient_id,
                                $patientDocument->created_at,
                                'patient_assessment_forms'
                            );
                        }
                    }

                    PatientElectronicDocument::where('id', $patientDocument->id)->update(['document_version' => 2.0]);
                }
            }, 'patient_electronic_documents.id', 'id');
    }
}
