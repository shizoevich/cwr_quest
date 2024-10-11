<?php

namespace App\Traits\GoogleDrive;

use App\Models\FaxModel\Fax;

use Illuminate\Support\Facades\Storage;
use App\Traits\GoogleDrive\DocumentTypes;

trait CopyFaxDocumentService
{
    use DocumentTypes;

    public function makeCopyFaxDocument($faxId, $faxCreationTime, $faxFileName)
    {
        $googleDriveFolderFaxes = config('google_drive_folder.faxes');
        $generateFileName = "Fax".' '.$faxCreationTime.' '.uniqid();

        // first step folder
        $centralFolderCwr = Storage::disk('google')->listContents('/'.$googleDriveFolderFaxes, false);
        $docCreatedYear = date('Y', strtotime($faxCreationTime));
        $docCreatedMonth = date('M', strtotime($faxCreationTime));

        if (array_search($docCreatedYear, array_column($centralFolderCwr, 'name')) === false) {
            Storage::disk('google')->createDir('/'.$googleDriveFolderFaxes.'/' . $docCreatedYear);
        }

        $docYearFolderArray = [];
        $centralFolderCwrChecked = Storage::disk('google')->listContents('/'.$googleDriveFolderFaxes, false);

        foreach ($centralFolderCwrChecked as $key => $value) {
            if ($centralFolderCwrChecked[$key]['name'] === $docCreatedYear) {
                $docYearFolderArray['year'] = $centralFolderCwrChecked[$key]['basename'];
            }
        } 
        // second step folder
        $yearFolderCwr = Storage::disk('google')->listContents('/'.$docYearFolderArray['year'], false);

        if (array_search($docCreatedMonth, array_column($yearFolderCwr, 'name')) === false) {
            Storage::disk('google')->createDir('/'.$docYearFolderArray['year'].'/' . $docCreatedMonth);
        }

        $docMonthFolderArray = [];
        $yearFolderCwrChecked = Storage::disk('google')->listContents('/'.$docYearFolderArray['year'], false);

        foreach ($yearFolderCwrChecked as $key => $value) {
            if ($yearFolderCwrChecked[$key]['name'] === $docCreatedMonth) {
                $docMonthFolderArray['month'] = $yearFolderCwrChecked[$key]['basename'];
            }
        }

       // last step
        if (Storage::disk('faxes')->exists($faxFileName)) {
            Storage::disk('google')->put($docMonthFolderArray['month'] . "/" . $generateFileName . '.pdf', Storage::disk('faxes')->get($faxFileName));
            Fax::query()->where('id', $faxId)->update(['google_drive' => true]);
        }
    }
}
