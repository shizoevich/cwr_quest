<?php

namespace App\Console\Commands\SingleUse;

use App\Appointment;
use App\AssessmentForm;
use App\Models\Billing\BillingPeriod;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientDocument;
use App\PatientDocumentType;
use App\PatientDocumentUploadInfo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttachInitialAssessmentsToAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ia:attach';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';
    
    private $startDate;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->startDate = Carbon::parse(BillingPeriod::DEFAULT_START_DATE)->startOfDay();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Appointment::query()
            ->each(function ($appointment) {
                $appointment->update([
                    'initial_assessment_type' => null,
                    'initial_assessment_id' => null,
                    'initial_assessment_created_at' => null,
                ]);
            });
        $this->processPatientElectronicDocuments();
        $this->processPatientDocuments();
    }
    
    private function processPatientElectronicDocuments()
    {
        PatientElectronicDocument::query()
//            ->whereDate('created_at', '>=', $this->startDate->toDateString())
            ->whereIn('document_type_id', AssessmentForm::getFileTypeIDsLikeInitialAssessment())
            ->each(function(PatientElectronicDocument $document) {
                $document->attachToAppointment(Carbon::now());
            }); 
    }
    
    private function processPatientDocuments()
    {
        PatientDocument::query()
//            ->whereDate('created_at', '>=', $this->startDate->toDateString())
            ->whereIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeInitialAssessment())
            ->each(function(PatientDocument $document) {
                $document->attachToAppointment(Carbon::now());
            });;
    }
}
