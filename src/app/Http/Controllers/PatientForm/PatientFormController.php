<?php

namespace App\Http\Controllers\PatientForm;

use App\Http\Controllers\Utils\AccessUtils;
use App\Http\Requests\PatientForms\UploadPhoto;
use App\Models\Patient\PatientForm;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\PatientDocumentType;
use Illuminate\Contracts\Encryption\DecryptException;


/**
 * Class PatientFormController
 * @package App\Http\Controllers\PatientForm
 */
class PatientFormController extends Controller
{
    use AccessUtils;
    
    
    /**
     * @param UploadPhoto $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPhoto(UploadPhoto $request)
    {
        try {
            $patient = Patient::findOrFail(decrypt($request->patient_id));
        } catch (DecryptException $exception) {
            abort(404);
        }
        
        $photo = $request->file('patient_photo');
        
        $ext = $photo->getClientOriginalExtension();
        $fileName = md5(uniqid(time())) . '.' . $ext;
        $isUploadedSuccess = Storage::disk('patients_docs')->put($fileName, file_get_contents($photo));
        if ($isUploadedSuccess) {
            $responseMessage = "The picture has been successfully uploaded.";
            $otherDocumentTypeID = PatientDocumentType::getImageId();
            $patientForm = new PatientForm();
            $patientForm->data = [
                'aws_document_name' => $fileName,
                'original_document_name' => 'Photo.' . $ext,
                'visible' => true,
                'document_type_id' => $otherDocumentTypeID
            ];
            $patientForm->status = PatientForm::STATUS_NEW;
            $patientForm->type = PatientForm::TYPE_PICTURE;
            $patient->patientForms()->save($patientForm);
        } else {
            $responseMessage = "Error. Please try again.";
        }
        
        return response()->json([
            'success' => $isUploadedSuccess,
            'message' => $responseMessage,
        ], 201);
    }
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $dateTo = $request->input('date_to');
        $dateFrom = $request->input('date_from');
        $statusCheck = $status = $request->input('status');
        $month = $request->input('month');
        $user = auth()->user();
        
        $patientForms = PatientForm::query()
            ->where('visible_in_patient_forms_page', 1)
            ->when(!$user->isAdmin(), function($query) use ($user) {
                $query->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patient_id')
                    ->where('patients_has_providers.chart_read_only', '=', 0)
                    ->where('providers_id', '=', $user->provider_id);
            })
            ->leftJoin('patients', 'patients.id', '=', 'patient_forms.patient_id')
            ->when($dateTo, function ($query, $dateTo) use ($dateFrom) {
                return $query->whereBetween('patient_forms.created_at',
                    [Carbon::createFromFormat('m/d/Y', $dateFrom), Carbon::createFromFormat('m/d/Y', $dateTo)]);
            })
            ->when($dateFrom, function ($query, $dateFrom) use ($dateTo) {
                if (!$dateTo) {
                    $from = (new Carbon($dateFrom))->startOfDay();
                    $to = (new Carbon($dateFrom))->endOfDay();
                    return $query->whereBetween('patient_forms.created_at', [$from, $to]);
                }
            })
            ->when($month, function ($query, $month) {
                $from = (new Carbon($month))->startOfMonth();
                $to = (new Carbon($month))->endOfMonth();
                return $query->whereBetween('patient_forms.created_at', [$from, $to]);
            })
            ->when($statusCheck != null, function ($query, $statusCheck) use ($status) {
                return $query->where('patient_forms.status', '=', $status);
            })
            ->orderByDesc('patient_forms.created_at')
            ->get(['patient_forms.*', 'patients.first_name', 'patients.last_name']);
        
        $patientFormsGrouped = $patientForms->groupBy(function ($item, $value) {
            return Carbon::parse($item['created_at'])->firstOfMonth()->toDateString();
        });
        
        return response()->json(['patient_forms' => $patientFormsGrouped->toArray()]);
        
    }
}
