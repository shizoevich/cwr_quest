<?php

namespace App\Helpers;

use App\Jobs\Documents\PrepareDocumentDownload;
use App\PatientDocument;
use App\Provider;
use Illuminate\Support\Facades\Storage;

class TridiuumDischargeHelper
{
    public static function getProviderFromDischargeSummaryDocument(PatientDocument $document): ?Provider
    {
        $preparedData = \Bus::dispatchNow(new PrepareDocumentDownload($document->id));

        if (empty($preparedData) || $preparedData === -1) {
            return null;
        }

        $tempFileName = md5(uniqid(time())) . '.pdf';

        Storage::disk('temp_pdf')->put($tempFileName, $preparedData['file']);

        // for local usage on Windows
        // $pdf = new \TonchikTm\PdfToHtml\Pdf(storage_path(Storage::disk('temp_pdf')->path($tempFileName)), [
        //     'pdftohtml_path' => 'D:\programs\poppler-0.89.0\bin\pdftohtml',
        //     'pdfinfo_path' => 'D:\programs\poppler-0.89.0\bin\pdfinfo',
        // ]);

        $pdf = new \TonchikTm\PdfToHtml\Pdf(Storage::disk('temp_pdf')->path($tempFileName), [
            'pdftohtml_path' => '/usr/bin/pdftohtml',
            'pdfinfo_path' => '/usr/bin/pdfinfo',
        ]);

        try {
            $pages = $pdf->getHtml()->getAllPages();
            $lastPage = end($pages);
        } catch (\Exception $e) {
            Storage::disk('temp_pdf')->delete($tempFileName);

            return null;
        }

        $doc = new \DOMDocument();
        $doc->loadHTML($lastPage);
        $tags = $doc->getElementsByTagName("p");

        $providerName = null;

        foreach ($tags as $tag) {
            if ($tag->getAttribute('class') === 'ft00') {
                $providerName = optional($tag->firstChild->firstChild)->data;
                break;
            }
        }

        Storage::disk('temp_pdf')->delete($tempFileName);

        if (!$providerName) {
            return null;
        }

        $providerName = Provider::sanitizeTridiuumProviderName($providerName);

        return Provider::withTrashed()->search($providerName)->first();
    }
}