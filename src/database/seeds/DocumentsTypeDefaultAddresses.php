<?php

use Illuminate\Database\Seeder;
use App\PatientDocumentType;
use App\PatientDocumentTypeDefaultAddresses;

class DocumentsTypeDefaultAddresses extends Seeder
{

    protected $types = [
        'KP Initial Assessment (Adult) - Panorama City' => [
            'email' => 'External-Referral-Team-STR@kp.org',
            'fax' => '8187581361'
        ],
        'KP Initial Assessment (Adult) - Woodland Hills' => [
            'email' => 'WH-OutsideMedicalCase-Management@kp.org',
            'fax' => ''
        ],
        'KP Initial Assessment (Child) - Woodland Hills' => [
            'email' => 'WH-OutsideMedicalCase-Management@kp.org',
            'fax' => ''
        ],
        'KP 1st Request for Reauthorization - Woodland Hills' => [
            'email' => 'WH-OutsideMedicalCase-Management@kp.org',
            'fax' => '8888964727'
        ],
        'KP Patient Discharge Summary - Woodland Hills' => [
            'email' => 'WH-OutsideMedicalCase-Management@kp.org',
            'fax' => ''
        ],
        'KP Patient Discharge Summary - Los Angeles' => [
            'email' => 'WH-OutsideMedicalCase-Management@kp.org',
            'fax' => ''
        ],
        'KP 2nd & Subsequent Requests - Woodland Hills' => [
            'email' => 'WH-OutsideMedicalCase-Management@kp.org',
            'fax' => ''
        ],
        'KP Request for Reauthorization - Panorama City' => [
            'email' => 'External-Referral-Team-STR@kp.org',
            'fax' => '8187581361'
        ],
        'KP Patient Discharge Summary - Panorama City' => [
            'email' => 'External-Referral-Team-STR@kp.org',
            'fax' => '8187581361'
        ]
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::delete('delete from patient_document_type_default_addresses');

        array_walk($this->types, function($type, $key){

            $documentType = PatientDocumentType::where('type', '=', $key)->first();

            $address = new PatientDocumentTypeDefaultAddresses([
                'email' => $type['email'],
                'fax'=> $type['fax']
            ]);
            $address->documentType()->associate($documentType);
            $address->save();
        });

    }
}
