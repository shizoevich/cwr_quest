<?php


namespace App\Http\Controllers\Api\Patient\DocumentRequest;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\ZipUtils;
use App\Http\Requests\Patient\DocumentRequest\GetFilledDocumentRequest;
use App\Http\Requests\Patient\DocumentRequest\Index;
use App\Http\Requests\Patient\DocumentRequest\SendRequest;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Models\Patient\DocumentRequest\PatientDocumentRequestItem;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Patient;
use App\Services\Patient\DocumentRequest\PatientDocumentRequest as DocumentService;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;
use App\Repositories\Patient\PatientDocumentRequestRepositoryInterface;

class PatientDocumentRequestController extends Controller
{
    use ZipUtils;

    /**
     * @var DocumentService
     */
    protected $documentService;
    protected $patientDocumentRequestRepository;

    public function __construct(PatientDocumentRequestRepositoryInterface $patientDocumentRequestRepository)
    {
        $this->documentService = new DocumentService();
        $this->patientDocumentRequestRepository = $patientDocumentRequestRepository;
    }

    /**
     * @param Index $request
     * @param Patient $patient
     *
     * @return JsonResponse
     */
    public function index(Index $request, Patient $patient)
    {
        $formTypes = PatientFormType::query()->visibleInTab()->orderBy('order')->get();
        $response = [];
        $forms = $patient->formRequests()->with(['sender', 'items', 'items.documents'])->get();
        $formTypes->each(function (PatientFormType $formType) use (&$response, $forms, $patient) {
            if ($formType->isCreditCardOnFile() && $patient->is_payment_forbidden) {
                return;
            }

            $formTypeId = $formType->getKey();
            $requests = [];
            $requestsWithType = $forms->filter(function ($form) use ($formTypeId) {
                return $form->items->filter(function ($item) use ($formTypeId) {
                    return $item->form_type_id === $formTypeId;
                })->count() > 0;
            });
            $requestsWithType->each(function ($requestWithType) use (&$requests, $formTypeId) {
                /** @var PatientDocumentRequest $requestWithType */
                $sender = $requestWithType->sender()->withTrashed()->first();
                if ($sender->isAdmin()) {
                    $sendBy = $sender->getFullName();
                } else {
                    $sendBy = ($sender->isProviderAttached() ? optional($sender->provider)->provider_name : null);
                }
                $documents = $requestWithType->items->filter(function ($item) use ($formTypeId) {
                    return $item->form_type_id === $formTypeId;
                })->first();
                $requestsItem = [
                    'id' => $requestWithType->id,
                    'send_by' => $sendBy,
                    'sent_at' => $requestWithType->created_at->toDateTimeString(),
                    'filled_at' => null,
                    'deferral_at' => $requestWithType->deferral_at,
                    'hard_bounced_at' => $requestWithType->hard_bounced_at,
                    'soft_bounced_at' => $requestWithType->soft_bounced_at,
                    'bounced_at' => $requestWithType->bounced_at,
                    'spam_at' => $requestWithType->spam_at,
                    'unsub_at' => $requestWithType->unsub_at,
                    'rejected_at' => $requestWithType->rejected_at,
                    'sent_to_email' => $requestWithType->sent_to_email,
                    'sent_to_phone' => $requestWithType->sent_to_phone,
                    'documents' => is_null($documents) ? null : $documents->documents->toArray(),
                    'items' => $requestWithType->items,
                ];
                $formFilled = $requestWithType->items->first(function ($value) use ($formTypeId) {
                    return $value->form_type_id === $formTypeId;
                });
                if ($formFilled) {
                    $requestsItem['filled_at'] = optional($formFilled->filled_at)->toDateTimeString();
                }
                $requests[] = $requestsItem;
            });
            $formType->has_filled_document = $formType->hasFilledDocument($patient);
            if ($formType->isSupportingDocuments()) {
                $formType->is_required = $formType->is_required && (!$formType->has_filled_document['has_insurance'] || !$formType->has_filled_document['has_driver_license']);
            } else {
                $formType->is_required = $formType->is_required && !$formType->has_filled_document;
            }
            $responseItem = $formType->toArray();
            $responseItem['requests'] = $requests;
            $response[] = $responseItem;
        });

        return response()->json($response);
    }

    /**
     * @param PatientDocumentRequest $documentRequest
     *
     * @return JsonResponse
     */
    public function show(PatientDocumentRequest $documentRequest)
    {
        if (isset($documentRequest->mandrill_event_id)) {
            if (empty($documentRequest->retrieve_count) || empty($documentRequest->opened_at)) {
                $documentRequest->opened_at = Carbon::now();
            }
        }
        $documentRequest->retrieve_count++;
        $documentRequest->last_retrieved_at = Carbon::now();
        $documentRequest->save();

        return response()->json([
            'document_request' => $documentRequest->getPublicResponse(),
        ]);
    }

    /**
     * @param Send $request
     * @param Patient      $patient
     *
     * @return JsonResponse
     */
    public function send(SendRequest $request, Patient $patient)
    {
        $data  = $request->only('forms');
        if ($request->input('send_via_email') && $request->input('email')) {
            $data['email'] = $request->input('email');
        }
        if ($request->input('send_via_sms') && $request->input('phone')) {
            $data['phone'] = $request->input('phone');
        }
        $documentRequest = $this->documentService->save($patient, $data);
        try {
            $this->documentService->sendViaSms($documentRequest);
        } catch (RestException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
        $this->documentService->sendViaEmail($documentRequest);

        return response()->json($documentRequest);
    }

    /**
     * @param GetFilledDocumentRequest $request
     * @param PatientDocumentRequest $documentRequest
     * @param PatientDocumentRequestItem $documentRequestItem
     * @return ResponseFactory|Application|BinaryFileResponse|Response
     */
    public function getDocumentsForItem(GetFilledDocumentRequest $request, PatientDocumentRequest $documentRequest, PatientDocumentRequestItem $documentRequestItem)
    {
        $documentsCount = $documentRequestItem->documents->count();
        if ($documentsCount === 0) {
            abort(Response::HTTP_NOT_FOUND);
        }
        if ($documentsCount === 1) {
            $document = $documentRequestItem->documents->first();
            $documentName = $document->original_document_name;
            $fileMimeType = Storage::disk('patients_docs')->mimeType($document->aws_document_name);

            return \Illuminate\Support\Facades\Response::make(Storage::disk('patients_docs')->get($document->aws_document_name), 200, [
                'Content-Type' => $fileMimeType,
                'Content-Disposition' => 'attachment; filename="' . $documentName . '"'
            ]);
        }
        $documentName = str_slug($documentRequestItem->type->title) . '.zip';
        $zip = $this->createPatientDocumentsArchive(
            $documentRequestItem->documents,
            md5($documentRequestItem->id) . '.zip'
        );

        return is_null($zip)
            ? response()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            : response()->download($zip, $documentName)->deleteFileAfterSend(true);
    }

    /**
     * Get count of signed and submitted patient forms in JSON format.
     * @param Patient $patient
     * @return JsonResponse
     */
    public function getPatientFormsCount(Patient $patient): JsonResponse
    {
        $patientFormsCount = $this->patientDocumentRequestRepository->patientFormsCount($patient);
        return response()->json($patientFormsCount);
    }
}
