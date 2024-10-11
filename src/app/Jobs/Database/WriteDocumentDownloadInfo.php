<?php

namespace App\Jobs\Database;

use App\PatientDocumentShared;
use App\PatientDocumentDownloadInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WriteDocumentDownloadInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $sharedDocument;


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

    /**
     * WriteDocumentSentComment constructor.
     *
     * @param PatientDocumentShared $sharedDocument
     */
    public function __construct(PatientDocumentShared $sharedDocument)
    {
        $this->sharedDocument = $sharedDocument;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $info = new PatientDocumentDownloadInfo;

        $info->patient_document_shared_id = $this->sharedDocument->id;
        $info->client_ip = $this->getClientIP();
        $info->client_user_agent = $_SERVER['HTTP_USER_AGENT'];

        $info->save();
    }
}
