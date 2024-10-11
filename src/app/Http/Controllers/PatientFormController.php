<?php

namespace App\Http\Controllers;

use App\Patient;
use App\PatientDocument;
use App\PatientDocumentType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientFormController extends Controller {

	public function index() {
		return view( 'forms.index' );
	}

	public function getPatient($id) {
		$model = Patient::where('patient_id', $id)
            ->with([
                'squareAccount',
                'squareAccount.cards',
                'informationForm'
            ])
            ->firstOrFail();
        
		$model->date_of_birth = Carbon::parse($model->date_of_birth)->format('m/d/Y');

		return $model;
	}

	public function uploadPhoto(Request $request) {
        $this->validate($request, [
            'patient_photo' => 'image',
            'patient_id' => 'required|numeric|exists:patients,id'
        ]);
        $photo = $request->file('patient_photo');
        $ext = $photo->getClientOriginalExtension();
        $fileName = md5(uniqid(time())) . '.' . $ext;
        $isUploadedSuccess = Storage::disk('patients_docs')->put($fileName, file_get_contents($photo));
        if($isUploadedSuccess) {
            $responseMessage = "The picture has been successfully uploaded.";
            $otherDocumentTypeID = PatientDocumentType::getImageId();
            $document = new PatientDocument([
                'aws_document_name' => $fileName,
                'original_document_name' => 'Photo.'.$ext,
                'visible' => true,
                'document_type_id' => $otherDocumentTypeID,
            ]);
            $patient = Patient::find($request->input('patient_id'));
            $patient->documents()->save($document);
        } else {
            $responseMessage = "Error. Please try again.";
        }
	    return response([
	        'success' => $isUploadedSuccess,
            'message' => $responseMessage,
        ]);
    }
}
