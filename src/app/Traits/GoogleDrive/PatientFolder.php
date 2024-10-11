<?php

namespace App\Traits\GoogleDrive;

use Illuminate\Support\Facades\Storage;

trait PatientFolder
{
    private $centralFolderCwr;

    public function getCentralFolder(bool $refresh = false): array
    {
        $googleDriveFolderPatients = config('google_drive_folder.patients');
        if (!$this->centralFolderCwr || $refresh) {
            $this->centralFolderCwr = Storage::disk('google')
                ->listContents('/' . $googleDriveFolderPatients, false);
        }
        return $this->centralFolderCwr;
    }
}