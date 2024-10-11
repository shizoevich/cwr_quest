<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\AssessmentForm;
use App\Events\NeedsWriteSystemComment;
use App\Events\PatientDocumentPreview;
use App\Events\PatientDocumentUpdate;
use App\Http\Controllers\Utils\AccessUtils;
use App\Jobs\Comments\WriteElectronicDocumentDownloadComment;
use App\Jobs\Documents\PrepareElectronicDocumentDownload;
use App\Jobs\Patients\UpdateDiagnoses;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Patient\ElectronicDocument\Download as DownloadRequest;
use App\Http\Requests\Patient\ElectronicDocument\Destroy as DestroyRequest;
use App\Traits\Patient\PatientDischargeTrait;

class PatientElectronicDocumentController extends Controller
{

    use PatientDischargeTrait, AccessUtils;

    public function show(Request $request, PatientElectronicDocument $document){

        $uniqueId = md5(uniqid(rand(), true));
        $document->isEditingAllowed = $this->isElectronicDocumentEditingAllowed($document->start_editing_date);
        $document->uniqueId = $uniqueId;
        $document->load('type');
        $document->diagnoses = $document->diagnoses()->withPivot('level')->get()->groupBy('pivot.level');
        event(new PatientDocumentPreview($document, true, $uniqueId));

        return $document->toJson();
    }
    
    private function prepareDiagnoses($selectedDiagnoses)
    {
        $diagnoseNames = array_map(function($item) {
            return $item['full_name'];
        }, $selectedDiagnoses);
        if($diagnoseNames) {
            return '"' . implode('","', $diagnoseNames) . '"';
        } else {
            return null;
        }
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        $data = $request->data;
        $data['start_editing_date'] = Carbon::now();
        $diagnosesPayload = null;
        $this->processingDiagnoses($diagnosesPayload, $data);
        
        $data['document_type_id'] = AssessmentForm::where('slug', $data['document_slug'])->first()->id;
        $document = PatientElectronicDocument::create($data);
        if($diagnosesPayload !== null) {
            \DB::table('patient_electronic_document_diagnoses')
                ->insert(array_map(function($diagnose) use ($document) {
                    $diagnose['patient_electronic_document_id'] = $document->getKey();
                    
                    return $diagnose;
                }, $diagnosesPayload));
            if(!empty($diagnosesPayload)) {
                dispatch(new UpdateDiagnoses($document->patient, array_unique(array_pluck($diagnosesPayload, 'diagnose_id')), false));
                $stats = $document->patient->diagnoses()->sync(array_unique(array_pluck($diagnosesPayload, 'diagnose_id')));
                if(count($stats['attached']) > 0 || count($stats['detached']) > 0) {
                    $userName = auth()->user()->provider ? auth()->user()->provider->provider_name : auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
                    event(new NeedsWriteSystemComment($document->patient->id, trans('comments.diagnose_changed_by_provider', ['provider_name' => $userName])));
                }
            }
        }
        $dischargeIDs = AssessmentForm::getFileTypeIDsLikeDischarge();
        if(in_array($document->document_type_id, $dischargeIDs)) {
            $patient = $document->patient;
            $dischargeStatusId = PatientStatus::getDischargedId();
            if($patient->status_id != $dischargeStatusId) {
                $this->discharge($patient, auth()->user()->provider);
            }
        }

        return response($document->toArray(), 201);
    }
    
    private function processingDiagnoses(&$diagnosesPayload, &$data)
    {
        $documentData = json_decode($data['document_data'], true);
        $diagnoseTypes = [
            [
                'level' => 1,
                'key' => 'selected_diagnoses',
                'diagnose_keys' => [],
            ],
            [
                'level' => 1,
                'key' => 'selected_diagnoses_1',
                'diagnose_keys' => [
                    'kp-initial-assessment-adult-wh' => 'primary',
                    'kp-initial-assessment-adult-la' => 'primary',
                    'kp-initial-assessment-adult-pc' => 'axis_1',
                    'kp-initial-assessment-child-pc' => 'axis_1',
                ],
            ],
            [
                'level' => 2,
                'key' => 'selected_diagnoses_2',
                'diagnose_keys' => [
                    'kp-initial-assessment-adult-wh' => 'secondary',
                    'kp-initial-assessment-adult-la' => 'secondary',
                    'kp-initial-assessment-adult-pc' => 'axis_2',
                    'kp-initial-assessment-child-pc' => 'axis_2',
                ],
            ],
            [
                'level' => 3,
                'key' => 'selected_diagnoses_3',
                'diagnose_keys' => [
                    'kp-initial-assessment-adult-wh' => 'notable_medical',
                    'kp-initial-assessment-adult-la' => 'notable_medical',
                    'kp-initial-assessment-adult-pc' => 'axis_3',
                    'kp-initial-assessment-child-pc' => 'axis_3',
                ],
            ],
        ];
        foreach ($diagnoseTypes as $type) {
            if(isset($documentData[$type['key']])) {
                if(!$diagnosesPayload) {
                    $diagnosesPayload = [];
                }
                if(array_key_exists($data['document_slug'], $type['diagnose_keys'])) {
                    $key = $type['diagnose_keys'][$data['document_slug']];
                } else {
                    $key = 'diagnosis_icd_code';
                }
                $documentData[$key] = $this->prepareDiagnoses($documentData[$type['key']]);
                $diagnosesPayload = array_merge($diagnosesPayload, array_map(function($diagnose) use ($type) {
                    return [
                        'diagnose_id' => $diagnose['id'],
                        'level' => $type['level'],
                    ];
                }, $documentData[$type['key']]));
                unset($documentData[$type['key']]);
            }
        }
        $data['document_data'] = json_encode($documentData);
    }

    /**
     * @param Request $request
     *
     * @param PatientElectronicDocument $document
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, PatientElectronicDocument $document)
    {
        $data = $request->data;
        $diagnosesPayload = null;
        $this->processingDiagnoses($diagnosesPayload, $data);
        
        $commentUniqueId = $request->comment_unique_id;

        if (md5($document->document_data) !== md5($data['document_data'])) {
            unset($data['patient_id']);
            $data['document_type_id'] = AssessmentForm::where('slug', $data['document_slug'])->first()->id;
            $document->update($data);
            if($diagnosesPayload !== null) {
                $document->diagnoses()->detach();
                \DB::table('patient_electronic_document_diagnoses')
                    ->insert(array_map(function($diagnose) use ($document) {
                        $diagnose['patient_electronic_document_id'] = $document->getKey();
            
                        return $diagnose;
                    }, $diagnosesPayload));
                if(!empty($diagnosesPayload)) {
                    dispatch(new UpdateDiagnoses($document->patient, array_unique(array_pluck($diagnosesPayload, 'diagnose_id')), true));
                }
            }
            event(new PatientDocumentUpdate($document, true, $commentUniqueId));
        }

        return response($document->toArray(), 200);
    }

    /**
     * @param DestroyRequest $request
     * @param PatientElectronicDocument $document
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy(DestroyRequest $request, PatientElectronicDocument $document)
    {
        $document->detachFromAppointment();
        $document->delete();

        return response([]);
    }

    /**
     * @param DownloadRequest $request
     * @param PatientElectronicDocument $document
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function download(DownloadRequest $request, PatientElectronicDocument $document)
    {
        $preparedData
            = \Bus::dispatchNow(new PrepareElectronicDocumentDownload($document->id));
        if ($preparedData === null) {
            return response(__('download.no_document'), 404);
        }
        \Bus::dispatchNow(new WriteElectronicDocumentDownloadComment($document, true));

        $cookie = cookie('document-download', "true", 0.05, null, null, false, false);

        return response($preparedData['file'], 200, [
            "Content-Type"        => $preparedData['mime'],
            "Content-disposition" => "attachment; filename=\"" . $preparedData['documentName'] . "\"",
        ])->cookie($cookie);
    }
}
