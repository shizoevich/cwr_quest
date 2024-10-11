<?php

namespace App\Helpers\Constant;

class DocZipArchiveConst
{
    const TYPE_INITIAL_ASSESSMENT = 'InitialAssessment';
    const TYPE_PATIENT_NOTE = 'PatientNote';
    const TYPE_DISCHARGE_SUMMARY = 'DischargeSummary';
    
    const ZIP_STORAGE = 'zip_archive';
    const PATIENT_DOC_ARCHIVE = 'DocsArchive_';
    const ZIP_FILE_PATH = 'app/public/zip_archive/';
    const COMMENT_PATIENT_CHART = 'Patient documents compiled, zipped, and sent to administrator via email.';
}
