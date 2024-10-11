<?php

namespace App\Console\Commands\SingleUse;

use App\AssessmentForm;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientNote;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SyncInitialAssessmentDiagnoses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'initial-assessment-diagnoses:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $cwrIaId = AssessmentForm::query()
            ->where('slug', 'cwr-initial-assessment')
            ->first()->id;
        $kpAdultPcId = AssessmentForm::query()
            ->where('slug', 'kp-initial-assessment-adult-pc')
            ->first()->id;
        $kpChildPcId = AssessmentForm::query()
            ->where('slug', 'kp-initial-assessment-child-pc')
            ->first()->id;
        $kpAdultWhId = AssessmentForm::query()
            ->where('slug', 'kp-initial-assessment-adult-wh')
            ->first()->id;
        $kpAdultLaId = AssessmentForm::query()
            ->where('slug', 'kp-initial-assessment-adult-la')
            ->first()->id;
        
        PatientElectronicDocument::query()
            ->where('document_type_id', $cwrIaId)
            ->each(function(PatientElectronicDocument $document) {
                $documentData = json_decode($document->document_data, true);
                if(empty($documentData['diagnosis_icd_code'])) {
                    return;
                }
                $diagnoses = explode('","', $documentData['diagnosis_icd_code']);
                foreach ($diagnoses as $diagnose) {
                    $parsedDiagnose = parse_diagnose($diagnose, true, true);
                    if ($parsedDiagnose && $parsedDiagnose->id) {
                        \DB::table('patient_electronic_document_diagnoses')->updateOrInsert([
                                'patient_electronic_document_id' => $document->getKey(),
                                'diagnose_id'     => $parsedDiagnose->getKey(),
                                'level' => 1,
                            ], [
                                'patient_electronic_document_id' => $document->getKey(),
                                'diagnose_id'     => $parsedDiagnose->getKey(),
                                'level' => 1,
                            ]);
                    } else {
                        $this->warn($document->getKey() . ' ' . $diagnose);
                    }
                }
            });
    
        PatientElectronicDocument::query()
            ->whereIn('document_type_id', [$kpAdultWhId, $kpAdultLaId])
            ->each(function(PatientElectronicDocument $document) {
                $documentData = json_decode($document->document_data, true);
                $types = [
                    'primary',
                    'secondary',
                    'notable_medical',
                ];
                
                foreach ($types as $level => $type) {
                    if(empty($documentData[$type])) {
                        continue;
                    }
                    $diagnoses = explode('","', $documentData[$type]);
                    foreach ($diagnoses as $diagnose) {
                        $parsedDiagnose = parse_diagnose($diagnose, true, true);
                        if ($parsedDiagnose && $parsedDiagnose->id) {
                            \DB::table('patient_electronic_document_diagnoses')->updateOrInsert([
                                'patient_electronic_document_id' => $document->getKey(),
                                'diagnose_id'     => $parsedDiagnose->getKey(),
                                'level' => ($level + 1),
                            ], [
                                'patient_electronic_document_id' => $document->getKey(),
                                'diagnose_id'     => $parsedDiagnose->getKey(),
                                'level' => ($level + 1),
                            ]);
                        } else {
                            $this->warn($document->getKey() . ' ' . $diagnose);
                        }
                    }
                    
                }
            });
    
        PatientElectronicDocument::query()
            ->whereIn('document_type_id', [$kpAdultPcId, $kpChildPcId])
            ->each(function(PatientElectronicDocument $document) {
                $documentData = json_decode($document->document_data, true);
                $types = [
                    'axis_1',
                    'axis_2',
                    'axis_3',
                ];
            
                foreach ($types as $level => $type) {
                    if(empty($documentData[$type])) {
                        continue;
                    }
                    $diagnoses = explode('","', $documentData[$type]);
                    foreach ($diagnoses as $diagnose) {
                        $parsedDiagnose = parse_diagnose($diagnose, true, true);
                        if ($parsedDiagnose && $parsedDiagnose->id) {
                            \DB::table('patient_electronic_document_diagnoses')->updateOrInsert([
                                'patient_electronic_document_id' => $document->getKey(),
                                'diagnose_id'     => $parsedDiagnose->getKey(),
                                'level' => ($level + 1),
                            ], [
                                'patient_electronic_document_id' => $document->getKey(),
                                'diagnose_id'     => $parsedDiagnose->getKey(),
                                'level' => ($level + 1),
                            ]);
                        } else {
                            $this->warn($document->getKey() . ' ' . $diagnose);
                        }
                    }
                
                }
            });
    }
}
