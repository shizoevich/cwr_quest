<?php

namespace App\Jobs\RingCentral;

use App\Models\FaxModel\Fax;
use App\Services\Ringcentral\RingcentralFax;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncCallFaxes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pageNumber;

    public function __construct($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $faxWebHookId = Fax::latest()->take(200)->pluck('fax_id_webhook');
        $faxApiServcie = new RingcentralFax();
        $recordsCount = count($faxApiServcie->faxList($this->pageNumber)['records']);
        $records = $faxApiServcie->faxList($this->pageNumber)['records'];
        for ($i = 0; $i < $recordsCount; $i++) {
            
            if (array_search($records[$i]['id'], $faxWebHookId->toArray()) === false) {

                //check ringcentral api data
                $phoneNumberFrom = isset($records[$i]["from"]["phoneNumber"]) ? $records[$i]["from"]["phoneNumber"] : null;
                $uri = isset($records[$i]["uri"]) ? $records[$i]["uri"] : null;
                $type =  isset($records[$i]['type']) ? $records[$i]['type'] : null;
                $creationTime =  isset($records[$i]['creationTime']) ? $records[$i]['creationTime'] : null;
                $priority =  isset($records[$i]['priority']) ? $records[$i]['priority'] : null;
                $direction =  isset($records[$i]['direction']) ? $records[$i]['direction'] : null;
                $availability =  isset($records[$i]['availability']) ? $records[$i]['availability'] : null;
                $subject =  isset($records[$i]['subject']) ? $records[$i]['subject'] : null;
                $messageStatus =  isset($records[$i]['messageStatus']) ?  $records[$i]['messageStatus'] : null;
                $faxResolution =  isset($records[$i]['faxResolution']) ? $records[$i]['faxResolution'] : null;
                $faxPageCount =  isset($records[$i]['faxPageCount']) ? $records[$i]['faxPageCount'] : null;
                $lastModifiedTime =  isset($records[$i]['lastModifiedTime']) ? $records[$i]['lastModifiedTime'] : null;

                $fax = Fax::create(
                    [
                        'phone' => $phoneNumberFrom,
                        'is_read' => false,
                        'provider_id' => null,
                        'patient_id' => null,
                        'uri' => $uri,
                        'extensionId' => $records[$i]['extensionId'],
                        'type' => $type,
                        'creationTime' =>  $creationTime,
                        'priority' => $priority,
                        'direction' => $direction,
                        'availability' =>  $availability,
                        'subject' =>   $subject,
                        'messageStatus' => $messageStatus,
                        'faxResolution' => $faxResolution,
                        'faxPageCount' => $faxPageCount,
                        'lastModifiedTime' => $lastModifiedTime,
                        'comment_id' => null,
                        'status_id' => null,
                        'file_name' =>  md5(uniqid(time())) . '.pdf',
                        'fax_id_webhook' => $records[$i]['id']
                    ]
                );
                if (isset($records[$i]['attachments']['0']['id'])) {
                    $uri = $records[$i]['attachments']['0']['uri'];
                    $faxApiServcie->pdfDownload($fax->file_name, $uri);
                }
            }
        }
    }
}
