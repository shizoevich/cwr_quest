<?php

namespace App\Traits\GoogleDrive;
use App\Helpers\Constant\PatientDocsConst;
trait DocumentTypes
{
    public function getDocumentTypes()
    {
        $documentTypeArray = [];

        $documentTypeArray = [
            "Patient Information-Informed Consent-Privacy Notice" => "2",
            "Authorization to Release Confidential Information" => "3",
            "Request for Reauthorization" => "12",
            "Kaiser" => "13",
            "KP Request for Reauthorization - Panorama City" => "14",
            "KP 1st Request for Reauthorization - Woodland Hills" => "15",
            "KP Patient Discharge Summary - Woodland Hills" => "16",
            "Discharge Summary" => "17",
            "CWR" => "18",
            "CWR Patient Discharge Summary" => "19",
            "Kaiser" => "20",
            "KP Patient Discharge Summary - Panorama City" => "21",
            "KP 2nd & Subsequent Requests - Woodland Hills" => "27",
            "Additional Document" => "28",
            "Image" => "29",
            "Initial Assessment" => "32",
            "CWR" => "33",
            "CWR Initial Assessment" => "34",
            "Kaiser" => "35",
            "KP Initial Assessment (Adult) - Panorama City" => "36",
            "KP Initial Assessment (Child) - Panorama City" => "37",
            "KP Initial Assessment (Adult) - Woodland Hills" => "38",
            "KP Initial Assessment (Child) - Woodland Hills" => "39",
            "Patient's Extended Signature Authorization" => "51",
            "Patient Rights" => "52",
            "Informed Consent - Old Version (5 pages)" => "53",
            "Authorization for Recurring Credit Card Charges" => "54",
            "KP Initial Assessment (Adult) - Los Angeles" => "57",
            "KP Initial Assessment (Child) - Los Angeles" => "58",
            "KP Request for Reauthorization - Los Angeles" => "59",
            "KP Patient Discharge Summary - Los Angeles" => "60",
            "Initial Assessment Tridiuum" => "61",
            "Discharge Summary Tridiuum" => "62",
            "Other Tridiuum" => "63",
            "Payment for Service and Fee Arrangements" => "64",
            "Agreement for Service & HIPAA Privacy Notice & Patient Rights & Notice to Psychotherapy Clients" => "65",
            "Supporting Document" => "66",
            "Telehealth" => "67",
            "Insurance" => "68",
            "Driver's License" => "69",
            "Fax" => "70"
        ];

        return $documentTypeArray;
    }

    public function getOtherTridiuum()
    {
        $documentOtherTridiuum = [];
        $documentOtherTridiuum = [
            "1" => "Patient Monitoring Report",
            "2" => "Provider Monitoring Report",
            "3" => "Q&A Report",
            "4" => "Screener Report",
            "5" => "Monitoring Report",
            "6" => "Adult (18+) Initial Evaluation",
            "7" => "Child Initial Evaluation",
            "8" => "Patient Intake Report",
            "9" => "Provider Intake Report",
            "10" => "Intake Report",
        ];
        return $documentOtherTridiuum;
    }

    public function getAssementFormTypes()
    {
        $assementFormTypes = [];
        $assementFormTypes = [
            "Initial Assessment" => "1",
            "CWR" => "2",
            "CWR Initial Assessment" => "3",
            "Kaiser" => "4",
            "KP Initial Assessment (Adult) - Panorama City" => "5",
            "KP Initial Assessment (Child) - Panorama City" => "6",
            "KP Initial Assessment (Adult) - Woodland Hills" => "7",
            "KP Initial Assessment (Child) - Woodland Hills" => "8",
            "Request for Reauthorization" => "9",
            "Kaiser" => "10",
            "KP Request for Reauthorization - Panorama City" => "11",
            "KP 1st Request for Reauthorization - Woodland Hills" => "12",
            "KP 2nd & Subsequent Requests - Woodland Hills" => "13",
            "Discharge Summary" => "14",
            "CWR" => "15",
            "CWR Patient Discharge Summary" => "16",
            "Kaiser" => "17",
            "KP Patient Discharge Summary - Panorama City" => "18",
            "KP Patient Discharge Summary - Woodland Hills" => "19",
            "Request for Referral for Returning Patients" => "20",
            "Kaiser" => "21",
            "KP Behavioral Health - Panorama City" => "22",
            "Medication Evaluation Referral" => "23",
            "Kaiser Woodland Hills" => "24",
            "KP Initial Assessment (Adult) - Los Angeles" => "25",
            "KP Initial Assessment (Child) - Los Angeles" => "26",
            "KP Request for Reauthorization - Los Angeles" => "27",
            "Kaiser Panorama City" => "28",
            "KP Patient Discharge Summary - Los Angeles" => "29",
            "Referral to BHIOS" => "30",
            "Kaiser Woodland Hills" => "31",
            "Referral for Groups" => "32",
            "Kaiser Los Angeles" => "33",
            "Referral to Higher Level of Care" => "34",
            "Kaiser Los Angeles" => "35",
            "Kaiser Los Angeles" => "36",
            "KPEP Kaiser Permanente Couples Counseling Referral" => "77",
            "KPEP Kaiser Permanente Group Referral" => "78",
            "KPEP Kaiser Permanente Intensive Treatment Referral" => "79",
            "KPEP Kaiser Permanente Medication Consultation Referral" => "80",
            "Patient Note" => '888'
        ];

        return $assementFormTypes;
    }

}


