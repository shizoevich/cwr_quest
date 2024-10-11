<?php

namespace App\Traits\GoogleDrive;

use App\Provider;
use App\User;
use App\UserMeta;
use Illuminate\Support\Facades\Storage;
use App\Traits\GoogleDrive\DocumentTypes;

trait CopyUserMetaSignatureService
{
    use DocumentTypes;

    public function makeCopyPatientFormDocument($userMetaCreatedAt, $userMetaUserId, $userMetaSignature)
    {
        $googleDriveFolderFaxes = config('google_drive_folder.therapists');
        $typeSubFolderName = 'Signature';

        $documentCreatedData = date('m-d-Y', strtotime($userMetaCreatedAt));

        $thearpistsFullName = Provider::query()->find(User::query()->find($userMetaUserId)->provider_id)->provider_name;

        $generateFileName = $typeSubFolderName . ' - ' . $thearpistsFullName . ' ' . $documentCreatedData;

        $centralFolderCwr = Storage::disk('google')->listContents('/'.$googleDriveFolderFaxes, false);
      
        $docCreatedYear = date('Y', strtotime($userMetaCreatedAt));

        if (array_search($thearpistsFullName, array_column($centralFolderCwr, 'name')) === false) {
            Storage::disk('google')->createDir($googleDriveFolderFaxes.'/' . $thearpistsFullName);
        }

        $thearpistsFullNameArray = [];
        $centralFolderCwrChecked = Storage::disk('google')->listContents('/'.$googleDriveFolderFaxes, false);
        foreach ($centralFolderCwrChecked as $key => $value) {
            if ($centralFolderCwrChecked[$key]['name'] === $thearpistsFullName) {
                $thearpistsFullNameArray['fullname'] = $centralFolderCwrChecked[$key]['basename'];
            }
        }
        $thearpistsFullNameFolder = Storage::disk('google')->listContents('/' . $thearpistsFullNameArray['fullname'], false);

        if (array_search($typeSubFolderName, array_column($thearpistsFullNameFolder, 'name')) === false) {
            Storage::disk('google')->createDir($thearpistsFullNameArray['fullname'] . '/' . $typeSubFolderName);
        }
        $thearpistsFullNameFolderChecked = Storage::disk('google')->listContents('/' . $thearpistsFullNameArray['fullname'], false);

        $docTypeFolderArray = [];
        foreach ($thearpistsFullNameFolderChecked as $key => $value) {
            if ($thearpistsFullNameFolderChecked[$key]['name'] === $typeSubFolderName) {
                $docTypeFolderArray['subFolderType'] = $thearpistsFullNameFolderChecked[$key]['basename'];
            }
        }
        $docTypeFolder = Storage::disk('google')->listContents('/' . $docTypeFolderArray['subFolderType'], false);

        if (array_search($docCreatedYear, array_column($docTypeFolder, 'name')) === false) {
            Storage::disk('google')->createDir($docTypeFolderArray['subFolderType'] . '/' . $docCreatedYear);
        }

        $docYearFolderArray = [];
        $docTypeFolderChecked = Storage::disk('google')->listContents('/' . $docTypeFolderArray['subFolderType'], false);
        foreach ($docTypeFolderChecked as $key => $value) {
            if ($docTypeFolderChecked[$key]['name'] === $docCreatedYear) {
                $docYearFolderArray['year'] = $docTypeFolderChecked[$key]['basename'];
            }
        }
        if(Storage::disk('signatures')->exists($userMetaSignature)){
            Storage::disk('google')->put($docYearFolderArray["year"] . "/" . $generateFileName . '.png', Storage::disk('signatures')->get($userMetaSignature));
        }
    } 
}

