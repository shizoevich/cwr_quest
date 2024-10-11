<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 30.09.2017
 * Time: 17:43
 */

namespace App\Http\Controllers\Utils;

use Illuminate\Support\Facades\Storage;
use App\UserMeta;

trait DocUtils
{
    /**
     * add signature in docx
     * @param $docName
     */
    private function signDocx($docName, $creatorID) {
        $assessmentDisk = Storage::disk('nextcloud');
        $signatureDisk = Storage::disk('signatures');
        $tempPdfDisk = Storage::disk('temp_pdf');
        $signatureName = UserMeta::where('user_id', $creatorID)->firstOrFail()->signature;

        if(!$assessmentDisk->has($docName)) {
            return null;
        }

        $tempPdfDisk->put($docName, $assessmentDisk->get($docName));
        $tempPdfDisk->put($signatureName, $signatureDisk->get($signatureName));
        $temp_root = config("filesystems.disks.temp_pdf.root");
        $zip = new \ZipArchive();
        $zip->open("$temp_root/$docName");
        $zip->deleteName('word/media/image1.png');
        $zip->addFile("$temp_root/$signatureName", 'word/media/image1.png');
        $zip->close();

        $assessmentDisk->put($docName, $tempPdfDisk->get($docName));

        $tempPdfDisk->delete($docName);
        $tempPdfDisk->delete($signatureName);
    }
}