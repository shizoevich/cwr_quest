<?php

namespace App\Http\Controllers;

use App\AssessmentForm;
use App\Events\PatientDocumentUpload;
use App\Helpers\NextcloudApi;
use App\Http\Requests\CreateAssessmentFormInNextcloud;
use App\Patient;
use App\PatientAssessmentForm;
use App\PatientStatus;
use App\Traits\Patient\PatientDischargeTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentFormsController extends Controller
{
    use PatientDischargeTrait;

    public static function indexTemplates(Request $request)
    {
        $dataset = AssessmentForm::query()
            ->orderBy('ind', 'asc')
            ->get()
            ->toArray();
        $mappedDataset = [];
        foreach($dataset as $item) {
            $item['parent'] = $item['parent'];
            $item['has_signature'] = $item['has_signature'];
            $mappedDataset[$item['id']] = $item;
        }

        $tree = [];

        foreach ($mappedDataset as $id => &$node) {
//            $id = $node['id'];
            $uid = uniqid();
            if ($node['parent'] === 0){
                $tree[$uid] = &$node;
            } else {
                $mappedDataset[$node['parent']]['childs'][$uid] = &$node;
            }
        }
        return $tree;
    }

    public static function storeToNextcloud(CreateAssessmentFormInNextcloud $request)
    {
        //admin haven't rights to store document
        if(Auth::user()->isAdmin()) {
            abort(403);
        }
        $patient = Patient::find($request->get('patient_id'));
        $dischargeIds = AssessmentForm::getFileTypeIDsLikeDischarge();
        if(in_array($request->get('assessment_form_id'), $dischargeIds)) {
            $visitCreatedCount = $patient->appointments()->onlyVisitCreated()->count();
            if($visitCreatedCount === 0) {
                return response([
                    'success' => false,
                    'message' => 'The patient does not have Visit Created appointments.',
                ], 405);
            }
        }
        $assessmentFormTemplate = AssessmentForm::find($request->get('assessment_form_id'));

        $davClient = new NextcloudApi();
        $folderName = 'patient_' . $patient->id;
        $createFolderResult = $davClient->createFolder($folderName);
        if ($createFolderResult['statusCode'] < 400 || $createFolderResult['statusCode'] == 405) {

            $path_parts = pathinfo($assessmentFormTemplate->internal_path());
            $fileName = uniqid() . '.' . $path_parts['extension'];

            $fileNextcloudPath = $folderName . '/' . $fileName;
            $result = $davClient->uploadFile($fileNextcloudPath, $assessmentFormTemplate->internal_path());
            if ($result['statusCode'] == 201) {

                $patientAssessmentForm = new PatientAssessmentForm([
                    'patient_id' => $patient->id,
                    'assessment_form_id' => $assessmentFormTemplate->id,
                    'file_nextcloud_path' => $fileNextcloudPath,
                    'status' => PatientAssessmentForm::STATUS_TEMP,
                    'has_signature' => $assessmentFormTemplate->has_signature,
                    'creator_id' => Auth::id(),
                ]);

                $patientAssessmentForm->save();

                $shareData = $davClient->shareFile($patientAssessmentForm->file_nextcloud_path);
                if ($shareData && count($shareData) > 0) {
                    $patientAssessmentForm->update([
                        'file_link' => $shareData['token'],
                        's3_file_id' => $shareData['file_source'],
                        'nextcloud_id'=> $shareData['nextcloud_id'],
                        'has_signature' => $assessmentFormTemplate->has_signature,
                    ]);
                }
            }
        }

        if (isset($patientAssessmentForm)) {

            $patientAssessmentForm->full_file_link = $patientAssessmentForm->getFileLink();

            event(new PatientDocumentUpload($patientAssessmentForm));

            return response($patientAssessmentForm->toArray());
        } else {
            return response([
                'message' => 'File is not created'
            ],500);
        }
    }

    public function save(Request $request, $formId)
    {
        //admin haven't rights to save document
        if(Auth::user()->isAdmin()) {
            abort(403);
        }
        $form = PatientAssessmentForm::findOrFail($formId);
        if($form) {
            $form->update([
                'status' => PatientAssessmentForm::STATUS_SAVED,
                'start_editing_date' => Carbon::now(),
            ]);
            $dischargeIDs = AssessmentForm::getFileTypeIDsLikeDischarge();
            $documentTypeID = $form->assessment_form_id;
            if(in_array($documentTypeID, $dischargeIDs)) {
                $patient = Patient::findOrFail($form->patient_id);
                $dischargeStatusId = PatientStatus::getDischargedId();
                if($patient->status_id != $dischargeStatusId) {
                    $this->discharge($patient);
                }
            }
        }

        return response($form);
    }

    public function get($id) {
        $form = PatientAssessmentForm::findOrFail($id);
        $form->full_file_link = $form->getFileLink();
        $form->is_existing_form = true;
        return response($form);
    }

}
