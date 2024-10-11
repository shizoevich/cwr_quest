<?php

namespace App\Jobs\Database;

use App\PatientLeadDocumentUploadInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WritePatientLeadDocumentUploadInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;

    /**
     * Function retrieve client ip from $_SERVER variable
     *
     * @return string
     */
    private function getClientIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return explode(',',$ipaddress)[0];
    }

    public function __construct($document)
    {
        $this->document = $document;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $info = PatientLeadDocumentUploadInfo::where(['patient_lead_document_id' => $this->document->id])->first();
        if(!$info) {
            $info = new PatientLeadDocumentUploadInfo;
        }
        $info->patient_lead_document_id = $this->document->id;
        $info->document_model = get_class($this->document);
        $info->user_id = \Auth::id();
        $info->client_ip = $this->getClientIP();
        $info->client_user_agent = $_SERVER['HTTP_USER_AGENT'];

        $info->save();
    }
}
