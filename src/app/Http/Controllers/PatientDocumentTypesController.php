<?php

namespace App\Http\Controllers;

use App\PatientDocumentType;
use App\PatientDocumentTypeDefaultAddresses;
use Illuminate\Http\Request;

class PatientDocumentTypesController extends Controller {

    public function getTree() {
        return PatientDocumentType::getTree();
    }

    public function getTypeID(Request $request) {
        $this->validate($request, [
            'type' => 'required|string|max:255'
        ]);
        return PatientDocumentType::select('id')->where('type', $request->input('type'))->first()['id'];
    }

    public function getDocumentDefaultEmails() {
        $emails = PatientDocumentTypeDefaultAddresses::select(['email', 'password'])
            ->whereNotNull('email')
            ->distinct()
            ->get();
        foreach($emails as $email) {
            if($email->email === 'External-Referral-Team-STR@kp.org') {
                $email->title = 'KP - Panorama City';
            } else if($email->email === 'WH-OutsideMedicalCase-Management@kp.org') {
                $email->title = 'KP - Woodland Hills';
            } else {
                $email->title = $email->email;
            }
        }

        return $emails;
    }

    public function getDocumentDefaultFaxes() {
        $faxes = PatientDocumentTypeDefaultAddresses::select(['fax'])
            ->whereNotNull('fax')
            ->distinct()
            ->get();
        foreach($faxes as $fax) {
            if($fax->fax === '8187581361') {
                $fax->title = 'KP - Panorama City';
            } else if($fax->fax === '8888964727') {
                $fax->title = 'KP - Woodland Hills';
            } else {
                $fax->title = $fax->fax;
            }
        }

        return $faxes;
    }

}
