<?php

namespace App\Jobs\Patients;

use App\Helpers\RetryJobQueueHelper;
use App\Models\Diagnose;
use App\Option;
use App\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateDiagnoses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * @var Patient
     */
    private $patient;
    /**
     * @var array
     */
    private $diagnoseIds;
    /**
     * @var bool
     */
    private $syncWithDatabase;
    
    /**
     * Create a new job instance.
     *
     * @param Patient $patient
     * @param array   $diagnoseIds
     * @param bool    $syncWithDatabase
     */
    public function __construct(Patient $patient, array $diagnoseIds, bool $syncWithDatabase = true)
    {
        $this->patient = $patient;
        $this->diagnoseIds = $diagnoseIds;
        $this->syncWithDatabase = $syncWithDatabase;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $diagnoses = Diagnose::getDiagnosesSavingOrder($this->diagnoseIds);

        $dataForUpdate = [
            'diagnoses' => $diagnoses->toArray()
        ];

        RetryJobQueueHelper::dispatchRetryUpdatePatient(Option::OA_ACCOUNT_2, $dataForUpdate, $this->patient->id);
        
        if ($this->syncWithDatabase) {
            $this->patient->diagnoses()->sync($diagnoses->pluck('id'));
        }
    }
}
