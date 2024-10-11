<?php

namespace App\Jobs\Documents;

use App\Http\Controllers\RemoveArrayItems;
use App\Http\Controllers\Utils\PdfUtils;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\PatientNote;

class PrepareNoteDownload extends DocumentDownload implements ShouldQueue
{
    use RemoveArrayItems, PdfUtils;

    /**
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|int|null|\Symfony\Component\HttpFoundation\Response
     */
    public function handle()
    {
        $id = intval($this->document_id);
        $filename = $id . ".pdf";

        $note = PatientNote::where('id', $id )->firstOrFail()->getAttributes();

        if(!$note['is_finalized']) {
            abort(403, 'Progress note is unfinalized');
        }

        if (!$this->disk->exists($filename)) {
            $noteCleared = $this->removeId($note);
            $noteCleared["id"] = $id ;
//            try {
//                $this->saveOrUpdateNoteAsPdfFillableForm($noteCleared);
                $this->generatePdfNoteOnFly($noteCleared);
//            } catch (\Exception $ex) {
//                return response($ex->getMessage(), 500);
//            }
        }

        if($this->isFileFaxSizeExceeds($filename)){
            return -1;
        }

        if($this->disk->exists($filename)){
            if(!is_null($note['date_of_service'])) {
                $note['date_of_service'] = Carbon::parse($note['date_of_service'])->format('m/d/Y');
            }
            $newDate = str_replace(['/', '-'], '_', $note['date_of_service']);
            $newDocumentName = 'PN_' . $note['first_name'] . '_' . $note['last_name'] . '_' . $newDate . '.pdf';
            $file = $this->disk->get($filename);

            return [
                'file' => $file,
                'mime' => 'application/pdf',
                'documentName' => $newDocumentName
            ];
        } else {
            return null;
        }

    }
}
