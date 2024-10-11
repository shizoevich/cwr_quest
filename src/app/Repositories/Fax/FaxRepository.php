<?php

namespace App\Repositories\Fax;

use App\Events\PatientDocumentUpload;
use App\Events\PatientLeadDocumentUpload;
use App\Helpers\Constant\LoggerConst;
use App\Helpers\Logger\LogActivityFax;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\FaxResource;
use App\Jobs\MakeImageThumbnail;
use App\Models\FaxModel\Fax;
use App\Models\FaxModel\FaxComment;
use App\Models\FaxModel\FaxStatus;
use App\Patient;
use App\PatientDocumentComment;
use App\PatientDocumentType;
use App\PatientLeadDocumentComment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHttpFoundation;

class FaxRepository implements FaxRepositoryInterface
{
    public function getFaxesForEntity($entity): array
    {
        $faxCollection = $entity->faxes()->orderBy('id', 'desc')->paginate();
        return $this->getFaxesData($faxCollection);
    }

    public function getFaxesData(LengthAwarePaginator $faxCollection): array
    {
        $baseUrl = $faxCollection->url($faxCollection->currentPage());
        $faxData = FaxResource::collection($faxCollection)->toArray(request());

        $meta = [
            "from" => $faxCollection->firstItem(),
            "last_page" => $faxCollection->lastPage(),
            "next_page_url" => $faxCollection->nextPageUrl(),
            "path" => $baseUrl,
            "per_page" => $faxCollection->perPage(),
            "prev_page_url" => $faxCollection->previousPageUrl(),
            "to" => $faxCollection->lastItem(),
            "total" => $faxCollection->total(),
        ];

        if (count($faxData)) {
            return [
                'message' => "show list of faxes",
                'status' => ResponseHttpFoundation::HTTP_OK,
                'meta' => $meta,
                'faxes' => $faxData,
            ];
        }

        return [
            'message' => "data api not found",
            'status' => ResponseHttpFoundation::HTTP_NOT_FOUND,
        ];
    }

    public function attachFax(array $data, $entity, Fax $fax): array
    {
        $this->createAndAttachFaxDocument($data, $entity, $fax);

        $faxComment = FaxComment::create(['description' => $data['comment'], 'fax_id' => $fax->id]);
        $faxCommentId = $faxComment->id;

        $statusId = FaxStatus::where('name', $data['status'])->first()->id;

        if ($entity instanceof Patient) {
            $fax->update([
                'patient_id' => $entity->id,
                'comment_id' => $faxCommentId,
                'status_id' => $statusId
            ]);
            $message = "Attach patient to fax";
            LogActivityFax::addToLog(LoggerConst::FAX_ATTACHED_TO_PATIENT, $fax->id, $entity->id);
        } else {
            $fax->update([
                'patient_lead_id' => $entity->id,
                'comment_id' => $faxCommentId,
                'status_id' => $statusId
            ]);
            $message = "Attach patient lead to fax";
            LogActivityFax::addToLog(LoggerConst::FAX_ATTACHED_TO_PATIENT_LEAD, $fax->id, null, $entity->id);
        }

        LogActivityFax::addToLog(LoggerConst::FAX_COMMENT, $fax->id);

        return ['message' => $message, 'status' => JsonResponse::HTTP_OK];
    }

    public function createAndAttachFaxDocument(array $data, $entity, Fax $fax)
    {
        $newFileName = $fax->file_name;
        $headers = [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $newFileName . '"',
        ];

        $file = Response::make(Storage::disk('faxes')->get($newFileName), 200, $headers);

        Storage::disk('patients_docs')->put($newFileName, $file);
        dispatch(new MakeImageThumbnail($newFileName));

        $documentData = [
            'aws_document_name' => $newFileName,
            'only_for_admin' => $data['only_for_admin'],
            'document_type_id' => PatientDocumentType::getFaxId(),
            'visible' => true
        ];

        if ($data['fax_name']) {
            $documentData['original_document_name'] = $data['fax_name'] . '.pdf';
        } else {
            $documentData['original_document_name'] = $newFileName;
        }

        $existingDocument = $entity->documents()->where('aws_document_name', $newFileName)->first();

        if ($existingDocument) {
            $document = $existingDocument;
        } else {
            $document = $entity->documents()->create($documentData);
        }

        if ($entity instanceof Patient) {
            PatientDocumentComment::create([
                'patient_documents_id' => $document->id,
                'admin_id' => Auth::user()->id,
                'content' => $data['comment'],
            ]);

            event(new PatientDocumentUpload($document));
        } else {
            PatientLeadDocumentComment::create([
                'patient_lead_documents_id' => $document->id,
                'admin_id' => Auth::user()->id,
                'content' => $data['comment'],
            ]);

            event(new PatientLeadDocumentUpload($document));
        }

        return $document;
    }

    public function detachFax($entity, $fax): array
    {
        $this->deleteFaxCommentsAndDocuments($entity, $fax);

        FaxComment::where('fax_id', $fax->id)->delete();

        $fax->update([
            'patient_id' => null,
            'patient_lead_id' => null,
            'status_id' => null,
            'comment_id' => null,
        ]);

        if ($entity instanceof Patient) {
            $message = "Dettach patient from fax";
            LogActivityFax::addToLog(LoggerConst::FAX_DETACHED_FROM_PATIENT, $fax->id, $fax->patient_id);
        } else {
            $message = "Dettach patient lead from fax";
            LogActivityFax::addToLog(LoggerConst::FAX_DETACHED_FROM_PATIENT_LEAD, $fax->id, null, $fax->patient_lead_id);
        }

        return ['message' => $message, 'status' => JsonResponse::HTTP_OK];
    }

    public function deleteFaxCommentsAndDocuments($entity, $fax): bool
    {
        $faxName = $fax->file_name;

        DB::table('fax_comments')->where('fax_id', $fax->id)->delete();

        $faxDocument = $entity->documents()->where('aws_document_name', $faxName)->first();

        if ($faxDocument) {
            if ($entity instanceof Patient) {
                PatientDocumentComment::where('patient_documents_id', $faxDocument->id)
                    ->each(function (PatientDocumentComment $comment) {
                        $comment->delete();
                    });
            } else {
                PatientLeadDocumentComment::where('patient_lead_documents_id', $faxDocument->id)
                    ->each(function (PatientLeadDocumentComment $comment) {
                        $comment->delete();
                    });
            }

            Storage::disk('patients_docs')->delete($faxDocument->aws_document_name);

            return $faxDocument->delete();
        }

        return false;
    }
}
