<?php


namespace App\Http\Controllers\Api\PatientForm;


use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\DocumentRequestSharedDocument\Store;
use App\Http\Requests\Patient\DocumentRequestSharedDocument\Show;
use App\Mail\Patient\DocumentRequest\FilledDocuments;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PatientDocumentRequestSharedDocumentController extends Controller
{
    
    /**
     * @param Store                  $request
     * @param Patient                $encryptedPatient
     * @param PatientDocumentRequest $documentRequest
     *
     * @return JsonResponse
     */
    public function store(Store $request, Patient $encryptedPatient, PatientDocumentRequest $documentRequest)
    {
        $payload = [
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'expiring_at' => Carbon::now()->addDays(config('patient_document_request.download_uri_lifetime'))->toDateTimeString(),
        ];
        $shared = $documentRequest->sharedDocuments()->create(array_merge($payload, [
            'hash' => md5(json_encode($payload) . uniqid() . time()),
        ]));
        \Mail::to($shared->email)->send(new FilledDocuments($shared));
        
        return response()->json(null, 201);
    }
    
    public function checkHashExpired(Request $request, PatientDocumentRequestSharedDocument $shared)
    {
        return response()->json(['expired' => $shared->isExpired()]);
    }
    
    /**
     * @param Show                                 $request
     * @param PatientDocumentRequestSharedDocument $shared
     *
     * @return JsonResponse
     */
    public function show(Show $request, PatientDocumentRequestSharedDocument $shared)
    {
        if(!$request->input('password') || !Hash::check($request->input('password'), $shared->password)) {
            abort(401);
        }
        
        return response()->json([
            'document_request' => $shared->documentRequest->getPublicResponse(),
        ]);
    }
}