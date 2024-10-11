<?php

namespace App\Traits\GoogleDrive;

use App\Patient;
use App\Traits\GoogleDrive\DocumentTypes;
use Illuminate\Support\Facades\Storage;

trait CopyPatientNoteAndAssessmentService
{
    use DocumentTypes, PatientFolder;

    public function makeCopyPatientNoteAndAssessment(
        $patientDocumentName,
        $patientDocumentTypeId,
        $patientDocumentPatientId,
        $patientDocumentCreatedAt,
        $documentStorage
    ) {
        $googleDriveFolderPatients = config('google_drive_folder.patients');
        //check the type of medical documents
        $medicalTypeOfDocument = [];
        $medicalTypeOfDocument['documentType'] = array_search($patientDocumentTypeId, $this->getAssementFormTypes());

        //change created_at column format data to america data format
        if (gettype($patientDocumentCreatedAt) === "string") {
            $documentCreatedData = date('m-d-Y', strtotime($patientDocumentCreatedAt));
        } else {
            $documentCreatedData = date('m-d-Y', strtotime($patientDocumentCreatedAt->toArray()['formatted']));
        }
        //patient full name
        $patientFullName = Patient::query()->find($patientDocumentPatientId)->full_name;

        $generateFileName = $medicalTypeOfDocument['documentType'] . ' - ' . $patientFullName . ' ' . $documentCreatedData;
        //year when created document
        $docCreatedYear = date('Y', strtotime($patientDocumentCreatedAt));

        //first step folder
        $centralFolderCwr = $this->getCentralFolder();

        if (array_search($patientFullName, array_column($centralFolderCwr, 'name')) === false) {
            Storage::disk('google')->createDir($googleDriveFolderPatients . '/' . $patientFullName);
            $centralFolderCwr = $this->getCentralFolder(true);
        }

        $patientFullNameArray = [];
       
        $keyPatientFullName = array_search($patientFullName, array_column($centralFolderCwr, 'name'));
        $patientFullNameArray['fullname'] = $centralFolderCwr[$keyPatientFullName]['basename'];

        //second step folder
        $patientFullNameFolder = Storage::disk('google')->listContents('/' . $patientFullNameArray['fullname'], false);

        if (array_search($medicalTypeOfDocument['documentType'], array_column($patientFullNameFolder, 'name')) === false) {
            Storage::disk('google')->createDir($patientFullNameArray['fullname'] . '/' . $medicalTypeOfDocument['documentType']);
            $patientFullNameFolderChecked = Storage::disk('google')->listContents('/' . $patientFullNameArray['fullname'], false);
        } else {
            $patientFullNameFolderChecked = $patientFullNameFolder;
        }
        
        $docTypeFolderArray = [];

        $keyDocTypeFolder =  array_search($medicalTypeOfDocument['documentType'], array_column($patientFullNameFolderChecked, 'name'));
        $docTypeFolderArray['subFolderType'] = $patientFullNameFolderChecked[$keyDocTypeFolder]['basename'];

        //third step folder
        $docTypeFolder = Storage::disk('google')->listContents('/' . $docTypeFolderArray['subFolderType'], false);

        if (array_search($docCreatedYear, array_column($docTypeFolder, 'name')) === false) {
            Storage::disk('google')->createDir($docTypeFolderArray['subFolderType'] . '/' . $docCreatedYear);
            $docTypeFolderChecked = Storage::disk('google')->listContents('/' . $docTypeFolderArray['subFolderType'], false);
        } else {
            $docTypeFolderChecked = $docTypeFolder;
        }

        $docYearFolderArray = [];

        $keyDocYearFolder = array_search($docCreatedYear, array_column($docTypeFolderChecked, 'name'));
        $docYearFolderArray['year'] = $docTypeFolderChecked[$keyDocYearFolder]['basename'];

        $docFormat = [];
        $patientDocumentNameArray = [];

        if ($patientDocumentTypeId == "888") {
            $docFormat['format'] = '.pdf';
            $patientDocumentNameArray['name'] = $patientDocumentName . '.pdf';
        } else {
            $docFormat['format'] = '.docx';
            $patientDocumentNameArray['name'] = $patientDocumentName . '.docx';
        }

        //last step - check if file exists in s3, then we copy file from s3 to google drive
        if (Storage::disk($documentStorage)->exists($patientDocumentNameArray['name'])) {
            Storage::disk('google')
                ->put(
                    $docYearFolderArray['year'] . "/" . $generateFileName . $docFormat['format'],
                    Storage::disk($documentStorage)->get($patientDocumentNameArray['name'])
                );
        }
    }
}
