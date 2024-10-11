<?php

use App\PatientDocumentType;
use Illuminate\Database\Seeder;

class PatientDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['id' => 2, 'type' => 'Patient Information / Informed Consent / Privacy Notice', 'parent' => 0, 'clickable' => 1, 'ind' => 1, 'type_id' => null],
            ['id' => 3, 'type' => 'Authorization to Release Confidential Information', 'parent' => 0, 'clickable' => 1, 'ind' => 4, 'type_id' => null],
            ['id' => 12, 'type' => 'Request for Reauthorization', 'parent' => 0, 'clickable' => 0, 'ind' => 8, 'type_id' => null],
            ['id' => 13, 'type' => 'Kaiser', 'parent' => 12, 'clickable' => 0, 'ind' => 1, 'type_id' => null],
            ['id' => 14, 'type' => 'KP Request for Reauthorization - Panorama City', 'parent' => 13, 'clickable' => 1, 'ind' => 1, 'type_id' => 2],
            ['id' => 15, 'type' => 'KP 1st Request for Reauthorization - Woodland Hills', 'parent' => 13, 'clickable' => 1, 'ind' => 2, 'type_id' => 2],
            ['id' => 16, 'type' => 'KP Patient Discharge Summary - Woodland Hills', 'parent' => 20, 'clickable' => 1, 'ind' => 2, 'type_id' => 3],
            ['id' => 17, 'type' => 'Discharge Summary', 'parent' => 0, 'clickable' => 0, 'ind' => 9, 'type_id' => null],
            ['id' => 18, 'type' => 'CWR', 'parent' => 17, 'clickable' => 0, 'ind' => 1, 'type_id' => null],
            ['id' => 19, 'type' => 'CWR Patient Discharge Summary', 'parent' => 18, 'clickable' => 1, 'ind' => 1, 'type_id' => 3],
            ['id' => 20, 'type' => 'Kaiser', 'parent' => 17, 'clickable' => 0, 'ind' => 2, 'type_id' => null],
            ['id' => 21, 'type' => 'KP Patient Discharge Summary - Panorama City', 'parent' => 20, 'clickable' => 1, 'ind' => 1, 'type_id' => 3],
            ['id' => 27, 'type' => 'KP 2nd & Subsequent Requests - Woodland Hills', 'parent' => 13, 'clickable' => 1, 'ind' => 3, 'type_id' => 2],
            ['id' => 28, 'type' => 'Additional Document', 'parent' => 0, 'clickable' => 1, 'ind' => 14, 'type_id' => null],
            ['id' => 29, 'type' => 'Image / Picture', 'parent' => 0, 'clickable' => 1, 'ind' => 15, 'type_id' => null],
            ['id' => 32, 'type' => 'Initial Assessment', 'parent' => 0, 'clickable' => 0, 'ind' => 7, 'type_id' => 1],
            ['id' => 33, 'type' => 'CWR', 'parent' => 32, 'clickable' => 0, 'ind' => 1, 'type_id' => 1],
            ['id' => 34, 'type' => 'CWR Initial Assessment', 'parent' => 33, 'clickable' => 1, 'ind' => 1, 'type_id' => 1],
            ['id' => 35, 'type' => 'Kaiser', 'parent' => 32, 'clickable' => 0, 'ind' => 2, 'type_id' => 1],
            ['id' => 36, 'type' => 'KP Initial Assessment (Adult) - Panorama City', 'parent' => 35, 'clickable' => 1, 'ind' => 1, 'type_id' => 1],
            ['id' => 37, 'type' => 'KP Initial Assessment (Child) - Panorama City', 'parent' => 35, 'clickable' => 1, 'ind' => 2, 'type_id' => 1],
            ['id' => 38, 'type' => 'KP Initial Assessment (Adult) - Woodland Hills', 'parent' => 35, 'clickable' => 1, 'ind' => 3, 'type_id' => 1],
            ['id' => 39, 'type' => 'KP Initial Assessment (Child) - Woodland Hills', 'parent' => 35, 'clickable' => 1, 'ind' => 4, 'type_id' => 1],
            ['id' => 51, 'type' => "Patient's Extended Signature Authorization", 'parent' => 0, 'clickable' => 1, 'ind' => 10, 'type_id' => null],
            ['id' => 52, 'type' => 'Patient Rights', 'parent' => 0, 'clickable' => 1, 'ind' => 11, 'type_id' => null],
            ['id' => 53, 'type' => 'Informed Consent - Old Version (5 pages)', 'parent' => 0, 'clickable' => 1, 'ind' => 12, 'type_id' => null],
            ['id' => 54, 'type' => 'Authorization for Recurring Credit Card Charges', 'parent' => 0, 'clickable' => 1, 'ind' => 13, 'type_id' => null],
            ['id' => 57, 'type' => 'KP Initial Assessment (Adult) - Los Angeles', 'parent' => 35, 'clickable' => 1, 'ind' => 5, 'type_id' => 1],
            ['id' => 58, 'type' => 'KP Initial Assessment (Child) - Los Angeles', 'parent' => 35, 'clickable' => 1, 'ind' => 6, 'type_id' => 1],
            ['id' => 59, 'type' => 'KP Request for Reauthorization - Los Angeles', 'parent' => 13, 'clickable' => 1, 'ind' => 4, 'type_id' => 2],
            ['id' => 60, 'type' => 'KP Patient Discharge Summary - Los Angeles', 'parent' => 20, 'clickable' => 1, 'ind' => 3, 'type_id' => 3],
            ['id' => 61, 'type' => 'Initial Assessment Tridiuum', 'parent' => 0, 'clickable' => 1, 'ind' => 16, 'type_id' => 1],
            ['id' => 62, 'type' => 'Discharge Summary Tridiuum', 'parent' => 0, 'clickable' => 1, 'ind' => 17, 'type_id' => 3],
            ['id' => 63, 'type' => 'Other Tridiuum', 'parent' => 0, 'clickable' => 1, 'ind' => 18, 'type_id' => null],
            ['id' => 64, 'type' => 'Payment for Service and Fee Arrangements', 'parent' => 0, 'clickable' => 1, 'ind' => 2, 'type_id' => null],
            ['id' => 65, 'type' => 'Agreement for Service & HIPAA Privacy Notice & Patient Rights & Notice to Psychotherapy Clients', 'parent' => 0, 'clickable' => 1, 'ind' => 3, 'type_id' => null],
            ['id' => 66, 'type' => 'Supporting Document', 'parent' => 0, 'clickable' => 0, 'ind' => 6, 'type_id' => null],
            ['id' => 67, 'type' => 'Telehealth Consent Form', 'parent' => 0, 'clickable' => 1, 'ind' => 5, 'type_id' => null],
            ['id' => 68, 'type' => 'Insurance', 'parent' => 66, 'clickable' => 1, 'ind' => 1, 'type_id' => null],
            ['id' => 69, 'type' => "Driver's License", 'parent' => 66, 'clickable' => 1, 'ind' => 2, 'type_id' => null],
            ['id' => 70, 'type' => 'Fax', 'parent' => 66, 'clickable' => 1, 'ind' => 2, 'type_id' => 1],
        ];
        
        foreach($types as $type) {
            PatientDocumentType::create($type);
        }
    }
}
