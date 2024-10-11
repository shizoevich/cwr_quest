<?php


namespace App\Http\Controllers\Api\Patient\Document;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\ZipUtils;
use App\Http\Requests\Api\PatientDocument\LoadTridiuumInitialAssessmentRequest;
use App\Http\Requests\Patient\Document\BaseDocumentOperationsRequest;
use App\Http\Requests\Patient\Document\GetDocumentBase64;
use App\Http\Requests\Patient\Document\SendViaEmail;
use App\Mail\PatientDocumentSend;
use App\Patient;
use App\PatientDocument;
use App\Repositories\Patient\PatientDocumentRepositoryInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class PatientDocumentController extends Controller
{
    use ZipUtils;

    protected $patientDocumentRepository;

    public function __construct(PatientDocumentRepositoryInterface $patientDocumentRepository)
    {
        $this->patientDocumentRepository = $patientDocumentRepository;
    }
    /**
     * @param GetDocumentBase64 $request
     * @param Patient $patient
     * @param PatientDocument $patientDocument
     * @return JsonResponse
     */
    public function getBase64(GetDocumentBase64 $request, Patient $patient, PatientDocument $patientDocument)
    {
        $disk = Storage::disk('patients_docs');
        if (!$disk->exists($patientDocument->aws_document_name)) {
            $documentBase64 = null;
        } else {
            $documentBase64 = base64_encode($disk->get($patientDocument->aws_document_name));
        }
        return response()->json([
            'document' => $documentBase64,
        ]);
    }

    /**
     * @param SendViaEmail $request
     * @param Patient $patient
     * @return JsonResponse
     */
    public function sendViaEmail(SendViaEmail $request, Patient $patient)
    {
        Mail::to($request->get('email'))
            ->send(new PatientDocumentSend(PatientDocument::query()->whereIn('id',
                $request->get('documents'))->get()));
        
        return response()->json([], 204);
    }

    /**
     * @param BaseDocumentOperationsRequest $request
     * @param Patient $patient
     * @return ResponseFactory|Application|BinaryFileResponse|Response
     */
    public function download(BaseDocumentOperationsRequest $request, Patient $patient)
    {
        $documents = PatientDocument::query()->whereIn('id', $request->get('documents'))->get();
        if ($documents->count() === 1) {
            $document = $documents->first();
    
            $fileMimeType = Storage::disk('patients_docs')->mimeType($document->aws_document_name);
    
            return \Illuminate\Support\Facades\Response::make(Storage::disk('patients_docs')->get($document->aws_document_name), 200, [
                'Content-Type' => $fileMimeType,
                'Content-Disposition' => 'attachment; filename="' . $document->original_document_name . '"'
            ]);
        }
        $zip = $this->createPatientDocumentsArchive($documents,
            md5(time() . $patient->id) . '.zip');
        
        return is_null($zip)
            ? response()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            : response()->download($zip)->deleteFileAfterSend(true);
    }

    public function loadTridiuumInitialAssessment(LoadTridiuumInitialAssessmentRequest $request)
    { 
        try {
            $data = $this->patientDocumentRepository->loadTridiuumInitialAssessment($request->validated());

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('notifications.error_syncing_initial_assessment_from_tridiuum'),
                'status' => 'error',
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], 500);
        }
    }
}
