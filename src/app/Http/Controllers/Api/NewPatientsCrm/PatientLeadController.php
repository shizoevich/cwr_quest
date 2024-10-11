<?php

namespace App\Http\Controllers\Api\NewPatientsCrm;

use App\Events\PatientLeadDocumentUpload;
use App\Http\Requests\Patient\Api\Index as IndexRequest;
use App\Http\Requests\PatientLead\Api\UploadFileRequest;
use App\Http\Requests\PatientLead\Api\SetDocumentTypeRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientLead\Api\AttachFaxRequest;
use App\Models\FaxModel\Fax;
use App\Models\Patient\Lead\PatientLead;
use App\PatientLeadDocumentUploadInfo;
use App\Repositories\Fax\FaxRepositoryInterface;
use App\Jobs\MakeImageThumbnail;
use App\PatientDocumentType;
use App\PatientLeadDocument;
use App\Repositories\NewPatientsCRM\PatientLead\PatientLeadRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PatientLeadController extends Controller
{
    protected $patientLeadRepository;
    protected $faxRepository;

    public function __construct(PatientLeadRepositoryInterface $patientLeadRepository, FaxRepositoryInterface $faxRepository)
    {
        $this->patientLeadRepository = $patientLeadRepository;
        $this->faxRepository = $faxRepository;
    }

    public function getInquirablesWithoutActiveInquiries(IndexRequest $request): JsonResponse
    {
        return response()->json($this->patientLeadRepository->getInquirablesWithoutActiveInquiries((int)$request->limit, $request->page, $request->search_query));
    }

    public function getFaxes(PatientLead $patientLead): JsonResponse
    {
        return response()->json($this->faxRepository->getFaxesForEntity($patientLead));
    }

    public function attachFax(AttachFaxRequest $request, PatientLead $patientLead, Fax $fax): JsonResponse
    {
        return response()->json($this->faxRepository->attachFax($request->validated(), $patientLead, $fax));
    }

    public function detachFax(PatientLead $patientLead, Fax $fax): JsonResponse
    {
        return response()->json($this->faxRepository->detachFax($patientLead, $fax));
    }

    public function uploadFile(UploadFileRequest $request, PatientLead $patientLead)
    {
        $file = $request->file('qqfile');
        $extension = $file->getClientOriginalExtension();
        $newFileName = md5(uniqid(time())) . '.' . $extension;
        $dischargeIDs = PatientDocumentType::getFileTypeIDsLikeDischarge();

        Storage::disk('patients_docs')->put($newFileName, file_get_contents($file));
        dispatch(new MakeImageThumbnail($newFileName));

        $document = $patientLead->documents()->create([
            'original_document_name' => $request->input('qqfilename'),
            'aws_document_name' => $newFileName,
            'visible' => false,
        ]);

        event(new PatientLeadDocumentUpload($document));

        return response()->json([
            'success' => true,
            'new_file_id' => $document->id,
            'discharge_ids' => $dischargeIDs,
        ], 201);
    }

    public function setDocumentType(SetDocumentTypeRequest $request, PatientLeadDocument $document)
    {
        $documentTypeID = $request->input('document_type_id');
        $document->document_type_id = $documentTypeID;
        $document->visible = true;
        $document->only_for_admin = $request->input('visible_only_for_admin');

        // if ($document->only_for_admin) {
        //     event(new PatientDocumentStatusChanged($document->id, $document->only_for_admin));
        // }

        $supportingDocumentId = PatientDocumentType::getSupportingDocumentId();
        if ($document->documentType->parent_id == $supportingDocumentId) {
            $document->other_document_type = $document->documentType->type;
        }

        $document->save();

        $documentUploader = null;

        $uploadInfo = PatientLeadDocumentUploadInfo::with(['user', 'user.meta', 'user.provider'])
            ->where('patient_lead_document_id', '=', $document->id)
            ->where('document_model', '=', PatientLeadDocument::class)
            ->first();

        if ($uploadInfo && $uploadInfo->user) {
            $documentUploader = $uploadInfo->user->isAdmin()
                ? $uploadInfo->user->getFullname()
                : optional($uploadInfo->user->provider)->provider_name;
        }

        $document->document_uploader = $documentUploader;

        return response()->json($document);
    }

    public function deleteDocument(PatientLeadDocument $document)
    {
        Storage::disk('patients_docs')->delete($document->aws_document_name);

        $status = $document->forceDelete();

        return response()->json([
            'success' => $status,
        ]);
    }
}
