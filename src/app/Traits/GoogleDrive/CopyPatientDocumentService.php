<?php

namespace App\Traits\GoogleDrive;

use App\Patient;
use App\Traits\GoogleDrive\DocumentTypes;
use Illuminate\Support\Facades\Storage;

trait CopyPatientDocumentService
{
    use DocumentTypes, PatientFolder;

    public function makeCopyPatientDocument(
        $patientDocumentOriginalDocumentName,
        $patientDocumentTypeId,
        $patientDocumentPatientId,
        $patientDocumentCreatedAt,
        $patientDocumentAwsOriginalName
    ) {
        $googleDriveFolderPatients = config('google_drive_folder.patients');
        //check the type of medical documents
        $medicalTypeOfDocument = [];
        if ($patientDocumentTypeId == '63') {
            if (
                preg_match('(Patient Monitoring)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['1'];
            } elseif (
                preg_match('(Provider Monitoring)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] =  $this->getOtherTridiuum()['2'];
            } elseif (
                preg_match('(Q&A Report)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['3'];
            } elseif (
                preg_match('(Screener Report)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['4'];
            } elseif (
                preg_match('(Monitoring Report)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['5'];
            } elseif (
                preg_match('(Patient Intake)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['8'];
            } elseif (
                preg_match('(Provider Intake)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['9'];
            } elseif (
                preg_match('(Intake Report)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['10'];
            } else {
                $medicalTypeOfDocument['documentType'] = 'Other Tridiuum';
            }
        } elseif ($patientDocumentTypeId == '61') {
            if (
                (preg_match('(Adult)', $patientDocumentOriginalDocumentName) === 1) &&
                (preg_match('(Evaluation)', $patientDocumentOriginalDocumentName) === 1)
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['6'];
            } elseif (
                preg_match('(Child Initial Evaluation)', $patientDocumentOriginalDocumentName) === 1
            ) {
                $medicalTypeOfDocument['documentType'] = $this->getOtherTridiuum()['7'];
            }else{
                $medicalTypeOfDocument['documentType'] = 'Initial Assessment Tridiuum';
            }
        } elseif ($patientDocumentTypeId == '2') {
            $medicalTypeOfDocument['documentType'] = 'Patient Information - Informed Consent - Privacy Notice';
        } else {
            $medicalTypeOfDocument['documentType'] = array_search($patientDocumentTypeId, $this->getDocumentTypes());
        }
        //check the format of medical documents like pdf, doc, docx or png
        $docFormat = [];
        if (preg_match('(.docx)', $patientDocumentAwsOriginalName) === 1) {
            $docFormat['format'] = '.docx';
        } elseif (preg_match('(.doc)', $patientDocumentAwsOriginalName) === 1) {
            $docFormat['format'] = '.doc';
        } elseif (preg_match('(.jpg)', $patientDocumentAwsOriginalName) === 1) {
            $docFormat['format'] = '.jpg';
        } elseif (preg_match('(.png)', $patientDocumentAwsOriginalName) === 1) {
            $docFormat['format'] = '.png';
        } elseif (preg_match('(.jpeg)', $patientDocumentAwsOriginalName) === 1) {
            $docFormat['format'] = '.jpeg';
        } elseif (preg_match('(.txt)', $patientDocumentAwsOriginalName) === 1) {
            $docFormat['format'] = '.txt';
        }else {
            $docFormat['format'] = '.pdf';
        }
        //change created_at column format data to america data format
        if (gettype($patientDocumentCreatedAt) === "string") {
            $documentCreatedData = date('m-d-Y', strtotime($patientDocumentCreatedAt));
        } else {
            $documentCreatedData = date('m-d-Y', strtotime($patientDocumentCreatedAt->toArray()['formatted']));
        }
        //patient full name
        $patientFullName = Patient::query()->find($patientDocumentPatientId)->full_name;
        //generate file name, that consist of - type of medical document, patient full name and created data of medical document
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
        }else{
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
        }else{
            $docTypeFolderChecked = $docTypeFolder;
        }

        $docYearFolderArray = [];
        
        $keyDocYearFolder = array_search($docCreatedYear, array_column($docTypeFolderChecked, 'name'));
        $docYearFolderArray['year'] = $docTypeFolderChecked[$keyDocYearFolder]['basename'];

        //last step - check if file exists in s3, then we copy file from s3 to google drive
        if (Storage::disk('patients_docs')->exists($patientDocumentAwsOriginalName)) {
            Storage::disk('google')->put($docYearFolderArray['year'] . "/" . $generateFileName . $docFormat['format'], Storage::disk('patients_docs')->get($patientDocumentAwsOriginalName));
        }
    }
}
