<?php

namespace App\Jobs\Documents;

use App\Http\Controllers\Utils\AccessUtils;
use App\PatientDocument;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use Illuminate\Contracts\Queue\ShouldQueue;


class PrepareDocumentDownload extends DocumentDownload implements ShouldQueue
{
    use AccessUtils;

    /**
     * @return array|int|null
     */
    public function handle()
    {
        $document = PatientDocument::withoutGlobalScope(DocumentsForAllScope::class)
            ->where('id', $this->document_id)
            ->first();
        $fileName = $document->aws_document_name;

        if (!$this->isUserHasAccessRightsForDocument($fileName)) {
            abort(403);
        }

        if($this->isFileFaxSizeExceeds($fileName)){
            return -1;
        }

        if ($this->disk->exists($fileName)) {
            $file = $this->disk->get($fileName);
            $mime         = $this->disk->mimeType($fileName);
            $originalName = $document->original_document_name;


            return [
                'file'       => $file,
                'mime'         => $mime,
                'documentName' => $originalName
            ];
        } else {
            return null;
        }


    }
}
