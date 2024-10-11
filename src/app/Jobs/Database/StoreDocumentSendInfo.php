<?php

namespace App\Jobs\Database;

use App\PatientDocument;
use App\PatientDocumentShared;
use App\PatientDocumentSharedLog;
use App\Provider;
use App\SharedDocumentMethod;
use App\SharedDocumentStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreDocumentSendInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Execute job
     *
     * @return PatientDocumentShared
     */
    public function handle()
    {
        if($this->input['document_model'] === 'PatientElectronicDocument') {
            $model = 'App\\Models\\Patient\\' . $this->input['document_model'];
        } else {
            $model = 'App\\' . $this->input['document_model'];
        }
        $sharedMethod = SharedDocumentMethod::where('method', $this->input['method'])->first();
        $sharedStatus = SharedDocumentStatus::where('status', '=','queue')->first();

        $sharedDocument = new PatientDocumentShared;

        $sharedDocument->patient_documents_id = $this->input['patient_documents_id'];
        $sharedDocument->document_model = $model;
        $sharedDocument->sharedMethod()->associate($sharedMethod);

        if($this->input['provider'] == null){
            $sharedDocument->admin()->associate($this->input['user']);
        } else{
            $sharedDocument->provider()->associate($this->input['provider']);
        }

        $sharedDocument->recipient   = $this->input['recipient'];
        $sharedDocument->shared_link = $this->input['shared_link'];

        if($this->input['shared_link_password'] !== null) {
            $sharedDocument->shared_link_password
                = bcrypt($this->input['shared_link_password']);
        }

        if(isset($this->input['external_id'])){
            $sharedDocument->external_id = $this->input['external_id'];
        }   

        $sharedDocument->save();

        $sharedDocumentLog = new PatientDocumentSharedLog;

        $sharedDocumentLog->patientDocumentShared()->associate($sharedDocument);
        $sharedDocumentLog->sharedStatus()->associate($sharedStatus);

        $sharedDocumentLog->save();

        return $sharedDocument;
    }
}

