<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\AssessmentForm;
use App\Components\Square\Customer;
use App\Components\Square\CustomerCard;
use App\Events\NeedsWriteSystemComment;
use App\Events\PatientDocumentPreview;
use App\Events\PatientDocumentStatusChanged;
use App\Events\PatientDocumentUpdate;
use App\Events\PatientDocumentUpload;
use App\Exceptions\Square\SquareException;
use App\Helpers\ImageHelper;
use App\Helpers\TridiuumDischargeHelper;
use App\Http\Controllers\Utils\AccessUtils;
use App\Http\Controllers\Utils\PdfUtils;
use App\Http\Requests\Patient\GetForSquare as GetForSquareRequest;
use App\Http\Requests\Patient\Show;
use App\Http\Requests\Patient\Update as UpdateRequest;
use App\Http\Requests\SyncPatientWithOfficeAllyRequest;
use App\Jobs\MakeImageThumbnail;
use App\Jobs\Parsers\SinglePatientDataParser;
use App\Jobs\Patients\UpdateDiagnoses;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Models\Patient\PatientTag;
use App\Models\Patient\PatientTransfer;
use App\Option;
use App\Patient;
use App\PatientAssessmentForm;
use App\PatientComment;
use App\PatientDiagnoseOld;
use App\PatientDocument;
use App\PatientDocumentType;
use App\PatientNote;
use App\PatientStatus;
use App\PatientVisitFrequency;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use App\Status;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use mikehaertl\pdftk\Pdf;
use App\Jobs\Documents\PrepareNoteDownload;
use App\Events\PatientDocumentDownloaded;
use App\Helpers\RetryJobQueueHelper;
use App\Http\Requests\Patient\Api\UploadFileRequest;
use App\Http\Requests\Patient\Api\SetDocumentTypeRequest;
use App\Http\Requests\Patient\Api\DeleteDocumentRequest;
use App\Jobs\Comments\ParseCommentMentions;
use App\Jobs\Documents\PrepareAssessmentDownload;
use App\Jobs\Documents\PrepareDocumentDownload;
use App\Models\FaxModel\Fax;
use App\Models\MandrillRejectedEmail;
use App\Models\TreatmentModality;
use App\PatientDocumentComment;
use App\Traits\Patient\PatientDischargeTrait;
use App\Traits\Patient\PatientStatisticsTrait;
use App\Repositories\Patient\PatientRepositoryInterface;

class PatientController extends Controller
{
    use PatientTrait, PatientDischargeTrait, PatientStatisticsTrait, RemoveArrayItems, PdfUtils, AccessUtils;

    /**
     * @var PatientRepositoryInterface
     */
    protected $patientRepository;

    /**
     * PatientController constructor.
     * @param PatientRepositoryInterface $patientRepository
     */
    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    private $pnExcept = [
        'statuses',
        'modal_title',
        'is_note',
        'is_finalized',
        'diagnosis_icd_code_fastselect',
        'date_of_service_fastselect',
        'dateOfServiceOptions',
        'dateOfServiceSelected',
        'comment_unique_id',
        'validation_message',
        'formatted_dos',
        'throttle_save',
        'is_autosave',
        'patient_id',
        'selected_diagnoses',
        'appointment',
    ];

    private function getDateOfService($date)
    {
        $temp = explode('/', $date);
        if (count($temp) === 3) {
            $m = intval($temp[0]);
            $d = intval($temp[1]);
            $y = intval($temp[2]);

            $date = Carbon::parse("$y-$m-$d");
            return $date;
        }
        return null;
    }

    /**
     * @param Show    $request
     * @param Patient $patient
     *
     * @return Patient
     */
    public function show(Show $request, Patient $patient)
    {
        $patient->load([
            'insurancePlan',
            'status',
            'providers' => function ($query) {
                $query->providerNames();
                $query->withTrashed();
            },
            'informationForm',
            'preprocessedBalance' => function ($query) {
                $query->select([
                    'patient_id',
                    'balance_after_transaction AS balance',
                ]);
            },
            'balance' => function ($query) {
                $query->select([
                    'patient_id',
                    'balance_after_transaction AS balance',
                ]);
            },
            'diagnoses',
            'preferredLanguage',
            'squareAccounts.cards',
            'additionalPhones',
            'tags',
            'therapyType',
            'visitFrequency',
            'visitFrequencyChanges',
            'visitFrequencyChanges.oldVisitFrequency',
            'visitFrequencyChanges.newVisitFrequency',
            'visitFrequencyChanges.changedBy' => function ($query) {
                $query->select([
                    'users_meta.user_id',
                    \DB::raw("COALESCE(providers.provider_name, CONCAT(users_meta.firstname, ' ', users_meta.lastname)) AS user_name"),
                ])
                ->leftJoin('users', 'users.id', '=', 'users_meta.user_id')
                ->leftJoin('providers', 'providers.id', '=', 'users.provider_id');
            },
        ]);

        $now = Carbon::now();
        $effStopDate = Carbon::parse($patient->eff_stop_date);
        $dateDiff = $now->diffInDays($effStopDate, false);
        $patient->is_eff_almost_overdue = isset($patient->insurancePlan) && $dateDiff <= $patient->insurancePlan->reauthorization_notification_days_count && $dateDiff >= 0;
        $patient->is_overdue = $dateDiff < 0;
        $patient->is_documents_uploading_allowed = $this->hasInitialAssessmentForm($patient->id)['response'];
        $currentTime = Carbon::now()->timestamp;
        //tab counts
        if ($request->input('with_tab_counts')) {
            $appointmentCounts = $patient->appointments()
                ->addSelect([
                    \DB::raw("COUNT(id) AS c"),
                    \DB::raw("IF(`time` > {$currentTime}, 'upcoming', 'past') AS is_upcoming")
                ])
                ->groupBy(\DB::raw("IF(`time` > {$currentTime}, 1, 0)"))
                ->pluck('c', 'is_upcoming')
                ->toArray();

            $patient->counts = [
                'upcoming_appointments' => data_get($appointmentCounts, 'upcoming', 0),
                'past_appointments' => data_get($appointmentCounts, 'past', 0),
                'visits' => count($this->getVisitCreatedAppointments($patient->id)),
                'alerts' => $patient->alerts()->count(),
            ];
        }
        if ($request->input('with_missing_forms')) {
            $missingForms = [];
            $formTypes = PatientFormType::query()->visibleInTab()->required()->orderBy('order')->get();
            $formTypes->each(function ($formType) use ($patient, &$missingForms) {
                if (!$formType->is_required) {
                    return;
                }
                $hasFilledDocument = $formType->hasFilledDocument($patient);
                if ($formType->name === 'supporting_documents') {
                    if (!$hasFilledDocument['has_insurance']) {
                        $missingForms[] = 'Supporting Documents (Insurance)';
                    }
                    if (!$hasFilledDocument['has_driver_license']) {
                        $missingForms[] = 'Supporting Documents (Driver\'s License)';
                    }
                } else if (!$hasFilledDocument) {
                    $missingForms[] = $formType->title;
                }
            });
            $patient->missing_forms = $missingForms;
        }

        $patient->cancelation_fee_info = $patient->canChargeLateCancellationFee();
        
        $patient->email_rejection_info = $this->getEmailRejectionInfo($patient->email);
        $patient->secondary_email_rejection_info = $this->getEmailRejectionInfo($patient->secondary_email);
        $patient->last_document_consent_info = $patient->lastDocumentConsentInfo();
        $patient->status_patient_info = $this->getPatientStatusData($patient->id);
        $patient->tridiuum_patient_id = optional($patient->tridiuumPatient)->external_id;

        return $patient;
    }

    private function getEmailRejectionInfo($email)
    {
        if (!$email) {
            return [
                'email_rejected' => false,
                'email_can_be_restored' => false
            ];
        }

        $rejectedEmail = MandrillRejectedEmail::where('email', $email)->first();
        if (!$rejectedEmail) {
            return [
                'email_rejected' => false,
                'email_can_be_restored' => false
            ];
        }

        return [
            'email_rejected' => $rejectedEmail->rejection_times > 0,
            'email_can_be_restored' => $rejectedEmail->rejection_times > 0 && !$rejectedEmail->is_restored
        ];
    }

    /**
     * @param        $patientId
     * @param Carbon $date
     * @param $providerId
     *
     * @return int|null
     */
    private function getAppointmentIdByDate($patientId, Carbon $date, $providerId)
    {
        return optional(
            Appointment::query()
                ->where('patients_id', $patientId)
                ->where('providers_id', $providerId)
                ->whereRaw("DATE(FROM_UNIXTIME(time)) = '" . $date->toDateString() . "'")
                ->first()
        )->getKey();
    }

    public function storeNote($request, $quickSave = false)
    {
        //admin haven't rights to save note
        if (Auth::user()->isAdmin()) {
            abort(403);
        }

        $model = null;
        $data = $request->except($this->pnExcept);
        //@todo delete diagnosis_icd_code field from patient_notes table
        $diagnoses = $request->input('selected_diagnoses') ?? [];
        $data['diagnosis_icd_code'] = array_map(function ($item) {
            return $item['full_name'];
        }, $diagnoses);
        $diagnosesIds = array_unique(array_pluck($diagnoses, 'id'));

        if ($data['diagnosis_icd_code']) {
            $data['diagnosis_icd_code'] = '"' . implode('","', $data['diagnosis_icd_code']) . '"';
        } else {
            $data['diagnosis_icd_code'] = null;
        }
        $data['start_editing_note_date'] = Carbon::now();
        if (isset($data['appointment_id']) && $data['appointment_id']) {
            $appointment = Appointment::query()->whereKey($data['appointment_id'])->first();
            if ($appointment) {
                $data['date_of_service'] = Carbon::createFromTimestamp($appointment->time);
            }
        } else {
            $data['date_of_service'] = null;
            $data['appointment_id'] = null;
        }

        $data['provider_id'] = Auth::user()->provider_id;

        $data['treatment_modality'] = isset($data['treatment_modality_id'])
            ? TreatmentModality::getTreatmentModalityNameById($data['treatment_modality_id'])
            : null;

        $data['note_version'] = 2.0;

        if (isset($request->is_note) && $request->is_note) {
            $model = PatientNote::findOrFail($request->input('id'));
            $requestData = $this->removeId($data);

            if ($quickSave) {
                if ($model->is_finalized) {
                    abort(403, 'This progress note already is finalized');
                }
            } else {
                $requestData['is_finalized'] = true;
                $now = Carbon::now();
                $requestData['finalized_at'] = $now;
                $requestData['start_editing_note_date'] = $now;
            }

            $model->update($requestData);
        } else {
            $model = Patient::findOrFail($request->input('patient_id'))->patientNotes();
            $requestData = $this->removeId($data);

            if (!$quickSave) {
                $requestData['is_finalized'] = true;
                $now = Carbon::now();
                $requestData['finalized_at'] = $now;
                $requestData['start_editing_note_date'] = $now;
            }

            $model = $model->create($requestData);
        }

        $model->syncDiagnoses($diagnosesIds);

        if ($model->is_finalized && !$quickSave) {
            $stats = $model->patient->diagnoses()->sync($diagnosesIds);
            if (count($stats['attached']) > 0 || count($stats['detached']) > 0) {
                $userName = auth()->user()->provider ? auth()->user()->provider->provider_name : auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
                event(new NeedsWriteSystemComment($model->patients_id, trans('comments.diagnose_changed_by_provider', ['provider_name' => $userName])));
            }
            dispatch(new UpdateDiagnoses($model->patient, $diagnosesIds, true));
        }

        event(new PatientDocumentUpload($model));

        return $model;
    }

    public function quickSaveNote(Request $request)
    {
        return $this->storeNote($request, true);
    }

    public function saveNote(Request $request)
    {
        $note = $this->storeNote($request);

        try {
            $this->generatePdfNoteOnFly($note->toArray());
        } catch (\Exception $ex) {
            \App\Helpers\SentryLogger::captureException($ex);
            return response($ex->getMessage(), 500);
        }

        return response(['success' => true]);
    }

    public function deleteUnfinalizedNotes(Request $request)
    {
        $this->validate($request, [
            'noteId' => 'required|numeric|exists:patient_notes,id',
            'patientId' => 'required|numeric|exists:patients,id',
        ]);

        $note = PatientNote::withTrashed()->find($request->input('noteId'));
        if ($note->deleted_at) {
            $success = true;
        } else if ($note->is_finalized || $note->patients_id !== $request->input('patientId')) {
            $success = false;
        } else {
            $success = $note->delete();
        }

        return response(['success' => $success]);
    }

    /**
     * function for converting sign from png file to pdf file with resize and set position
     *
     * @param $pngSign - name of png file e.g. in.png
     * @param $pdfSign - name of output pdf file e.g. out.pdf
     */
    private function convertPngToPdfSign($pngSign, $pdfSign)
    {
        Storage::disk('temp_pdf')->put($pngSign, Storage::disk('signatures')->get($pngSign));
        $fullPngSignPath = config("filesystems.disks.temp_pdf.root") . "/" . $pngSign;
        $fullPdfSignPath = config("filesystems.disks.temp_pdf.root") . "/" . $pdfSign;
        $cmd = "convert -page A4+250+150 -resize 120x68 -filter Gaussian -density 50x50  -brightness-contrast -100x100 ";
        $cmd = $cmd . $fullPngSignPath . " " . $fullPdfSignPath;
        exec($cmd);
    }

    /**
     * DEPRECATED
     * helper function which do all job with pdf fillable form
     *
     * @param $data
     */
    private function saveOrUpdateNoteAsPdfFillableForm($data)
    {
        $noteId = $data['id'];
        $userId = Auth::user()->id;
        //        fill form fields with given values
        $pdf = new Pdf(Storage::url('progress_note_template.pdf'));
        $mappedData = $this->mapToPdfFields($data);
        $pdf->fillForm($mappedData)->needAppearances()->execute();
        Storage::disk('temp_pdf')->put($noteId . '.pdf', File::get((string)$pdf->getTmpFile()));
        //        add signature to pdf
        $unsignedNoteFile = config("filesystems.disks.temp_pdf.root") . "/" . $noteId . ".pdf";
        $signedPdf = new Pdf($unsignedNoteFile);
        $providerSignPng = UserMeta::where('user_id', $userId)->firstOrFail()->signature;
        $providerSignPdf = substr($providerSignPng, 0, -3) . "pdf";
        $this->convertPngToPdfSign($providerSignPng, $providerSignPdf);
        $signedPdf->stamp(config("filesystems.disks.temp_pdf.root") . "/" . $providerSignPdf)->execute();
        Storage::disk('progress_notes')->put($noteId . ".pdf", File::get((string)$signedPdf->getTmpFile()));
        //        remove temp files
        Storage::disk('temp_pdf')->delete($noteId . ".pdf");
        Storage::disk('temp_pdf')->delete($providerSignPng);
        Storage::disk('temp_pdf')->delete($providerSignPdf);
    }

    /**
     * update note's db record and corresponding pdf file
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function updateNote(Request $request)
    {
        //admin haven't rights to update note
        if (Auth::user()->isAdmin() || !$this->isNoteEditingAllowed($request->input('id'))['allowed']) {
            abort(403);
        }
        $data = $request->except($this->pnExcept);
        //@todo delete diagnosis_icd_code field from patient_notes table
        $diagnoses = $request->input('selected_diagnoses') ?? [];
        $data['diagnosis_icd_code'] = array_map(function ($item) {
            return $item['full_name'];
        }, $diagnoses);
        $diagnosesIds = array_pluck($diagnoses, 'id');
        if ($data['diagnosis_icd_code']) {
            $data['diagnosis_icd_code'] = '"' . implode('","', $data['diagnosis_icd_code']) . '"';
        } else {
            $data['diagnosis_icd_code'] = null;
        }

        if ($data['date_of_service']) {
            $dateOfService = Carbon::createFromFormat('m/d/Y', $data['date_of_service']);
            $data['date_of_service'] = Carbon::parse($dateOfService)->format('Y-m-d');
        } else {
            $data['date_of_service'] = null;
        }
        $data['provider_id'] = (int)Auth::user()->provider_id;

        $data['treatment_modality'] = isset($data['treatment_modality_id'])
            ? TreatmentModality::getTreatmentModalityNameById($data['treatment_modality_id'])
            : null;

        $note = PatientNote::find($request->input('id'));
        if (isset($note)) {
            $noteKeys = array_only($note->toArray(), array_keys($data));

            if (md5(json_encode($noteKeys)) !== md5(json_encode($data))) {
                if ($data['date_of_service']) {
                    $data['date_of_service'] = $dateOfService;
                }

                $commentUniqueId = $request->comment_unique_id;

                $note->update($data);
                $note->syncDiagnoses($diagnosesIds);
                dispatch(new UpdateDiagnoses($note->patient, $diagnosesIds, true));
                event(new PatientDocumentUpdate($note, true, $commentUniqueId));
            }

            $data['finalized_at'] = $note->finalized_at;
        }

        // saving request data to default Storage
        try {
            // $this->saveOrUpdateNoteAsPdfFillableForm($request->all());
            $this->generatePdfNoteOnFly($data);
        } catch (\Exception $ex) {
            \App\Helpers\SentryLogger::captureException($ex);
            return response($ex->getMessage(), 500);
        }
    }

    /**
     * exportNote function -> download pdf file of requested note
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function exportNote($id)
    {
        $preparedData
            = \Bus::dispatchNow(new PrepareNoteDownload($id));

        $sharedDocument = PatientNote::where(
            'id',
            '=',
            $id
        )->first();

        if ($preparedData === null) {
            return response(__('download.no_document'), 404);
        } else {

            $file = $preparedData['file'];
            $mime = $preparedData['mime'];
            $documentName = $preparedData['documentName'];

            event(new PatientDocumentDownloaded($sharedDocument, true));
            $cookie = cookie('document-download', "true", 0.05, null, null, false, false);
            return response($file, 200, [
                "Refresh" => 0,
                "Content-Type" => $mime,
                "Content-disposition" => "attachment; filename=\"" . $documentName . "\"",
            ])->cookie($cookie);
        }
    }

    /**
     * add signature in docx
     *
     * @param $docName
     */
    private function signDocx($docName, $creatorID)
    {
        $assessmentDisk = Storage::disk('nextcloud');
        $signatureDisk = Storage::disk('signatures');
        $tempPdfDisk = Storage::disk('temp_pdf');
        $signatureName = UserMeta::where('user_id', $creatorID)->firstOrFail()->signature;

        $tempPdfDisk->put($docName, $assessmentDisk->get($docName));
        $tempPdfDisk->put($signatureName, $signatureDisk->get($signatureName));
        $temp_root = config("filesystems.disks.temp_pdf.root");
        $zip = new \ZipArchive();
        $zip->open("$temp_root/$docName");
        $zip->deleteName('word/media/image1.png');
        $zip->addFile("$temp_root/$signatureName", 'word/media/image1.png');
        $zip->close();

        $assessmentDisk->put($docName, $tempPdfDisk->get($docName));

        $tempPdfDisk->delete($docName);
        $tempPdfDisk->delete($signatureName);
    }

    public function getDocumentsThumbnail(Request $request)
    {
        $this->validate($request, [
            'patient_id' => 'required|numeric|exists:patients,id',
        ]);

        $patientId = $request->input('patient_id');

        if (!$this->isUserHasAccessRightsForPatient($patientId)) {
            abort(403);
        }

        $documents = PatientDocument::where('patient_id', $patientId)->get();

        $response = [];

        foreach ($documents as $document) {
            if (Storage::disk('patients_docs')->exists($document->aws_document_name)) {
                $preview = ImageHelper::getBase64ImageThumbnail($document->aws_document_name, false);
                if (!empty($preview)) {
                    $response[$document->aws_document_name] = $preview;
                }
            }
        }

        return $response;
    }


    public function downloadDocument($documentName)
    {
        $isNextcloud = (strpos($documentName, 'rn:oid') == 1);

        //abort 403 if user hasn't access rigths for this file
        if (!$isNextcloud && !$this->isUserHasAccessRightsForDocument($documentName)) {
            abort(403);
        }

        if ($isNextcloud) {
            $s3_file_id = explode(':', $documentName);
            $s3_file_id = array_pop($s3_file_id);
            $document = PatientAssessmentForm::where('s3_file_id', $s3_file_id)->first();

            $preparedData
                = \Bus::dispatchNow(new PrepareAssessmentDownload($document->id));

            if ($preparedData === null) {
                return response(__('download.no_document'), 404);
            }
        } else {

            $document = PatientDocument::withoutGlobalScope(DocumentsForAllScope::class)
                ->where('aws_document_name', $documentName)
                ->first();

            $preparedData
                = \Bus::dispatchNow(new PrepareDocumentDownload($document->id));

            if ($preparedData === null) {
                return response(__('download.no_document'), 404);
            }
        }

        $file = $preparedData['file'];
        $mime = $preparedData['mime'];
        $documentName = $preparedData['documentName'];

        event(new PatientDocumentDownloaded($document, true));

        $cookie = cookie('document-download', "true", 0.05, null, null, false, false);
        return response($file, 200, [
            "Content-Type" => $mime,
            "Content-disposition" => "attachment; filename=\"" . $documentName . "\"",
        ])->cookie($cookie);
    }

    public function previewDocument($documentName)
    {
        //abort 403 if user hasn't access rigths for this file
        if (!$this->isUserHasAccessRightsForDocument($documentName)) {
            abort(403);
        }

        $document = PatientDocument::withoutGlobalScope(DocumentsForAllScope::class)
            ->where('aws_document_name', $documentName)
            ->first();

        $file = Storage::disk('patients_docs')
            ->get($documentName);
        $fileMimeType = Storage::disk('patients_docs')->mimeType($documentName);

        event(new PatientDocumentPreview($document, true));

        $cookie = cookie('document-preview', "true", 0.05, null, null, false, false);
        return Response::make($file, 200, [
            'Content-Type' => $fileMimeType,
            'Content-Disposition' => 'inline; filename="' . $documentName . '"'
        ])->cookie($cookie);
    }

    public function uploadFile(UploadFileRequest $request)
    {
        $onlyForAdmin = $request->input('only_for_admin');
        $documentTypeIdParam = $request->input('document_type_id');
        $patientId = $request->input('patient_id');
        $faxId = $request->input('fax_id');
        $assign = $request->input('assign');
        $faxName = $request->input('fax_name');

        if (isset($assign)) {
            $newFileName = Fax::where('id', $faxId)->first()->file_name;
            $headers = [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $newFileName . '"',
            ];

            $file = Response::make(Storage::disk('faxes')->get($newFileName), 200, $headers);
            $patient = Patient::findOrFail($patientId);
            $dischargeIDs = $documentTypeIdParam;
        } else {
            $file = $request->file('qqfile');
            $extension = $file->getClientOriginalExtension();
            $newFileName = md5(uniqid(time())) . '.' . $extension;
            $patient = Patient::findOrFail($patientId);
            $dischargeIDs = PatientDocumentType::getFileTypeIDsLikeDischarge();
        }

        $hasInitialAssessmentFormResponse = $this->hasInitialAssessmentForm($patient->id);
        $hasInitialAssessmentForm = $hasInitialAssessmentFormResponse['response'];

        if ($hasInitialAssessmentForm) {
            if (isset($assign)) {
                Storage::disk('patients_docs')->put($newFileName, $file);
                dispatch(new MakeImageThumbnail($newFileName));

                if (isset($faxName)) {
                    $document = $patient->documents()->create([
                        'original_document_name' => $faxName . '.pdf',
                        'aws_document_name' => $newFileName,
                        'only_for_admin' => $onlyForAdmin,
                        'document_type_id' =>  $documentTypeIdParam
                    ]);
                } else {
                    $document = $patient->documents()->create([
                        'original_document_name' => $newFileName,
                        'aws_document_name' => $newFileName,
                        'only_for_admin' => $onlyForAdmin,
                        'document_type_id' =>  $documentTypeIdParam
                    ]);
                }

                $data['content'] = $request->input('content');
                $data['patient_documents_id'] = $document->id;
                $data['document_model'] = 'App\PatientDocument';
                $data['admin_id'] = Auth::user()->id;
                $comment = PatientDocumentComment::create($data);

                \Bus::dispatchNow(new ParseCommentMentions($data['content'], $comment->id, 'PatientDocumentComment', $patientId));
            } else {
                Storage::disk('patients_docs')->put($newFileName, file_get_contents($file));
                dispatch(new MakeImageThumbnail($newFileName));

                $document = $patient->documents()->create([
                    'original_document_name' => $request->input('qqfilename'),
                    'aws_document_name' => $newFileName,
                    'visible' => false,
                ]);
            }

            event(new PatientDocumentUpload($document));

            $visitCreatedCount = $patient->appointments()->onlyVisitCreated()->count();

            return response()->json([
                'success' => true,
                'new_file_id' => $document->id,
                'discharge_ids' => $dischargeIDs,
                'visit_created_count' => $visitCreatedCount,
            ], 201);
        }

        return response()->json([
            'success'            => false,
            'initial_assessment' => key_exists('initial_assessment', $hasInitialAssessmentFormResponse)
                ? $hasInitialAssessmentFormResponse['initial_assessment']
                : null,
        ], 201);
    }

    public function deleteDocument(DeleteDocumentRequest $request)
    {
        $document = PatientDocument::find($request->input('id'));

        Storage::disk('patients_docs')->delete($document->aws_document_name);

        $status = $document->forceDelete();
        
        return response()->json([
            'success' => $status,
        ]);
    }

    public function setDocumentType(SetDocumentTypeRequest $request)
    {
        $document = PatientDocument::find($request->input('document_id'));
        $documentTypeID = $request->input('document_type_id');
        $document->document_type_id = $documentTypeID;
        $document->visible = true;
        $document->only_for_admin = $request->input('visible_only_for_admin');
        if ($document->only_for_admin) {
            event(new PatientDocumentStatusChanged($document->id, $document->only_for_admin));
        }
        //        $otherTypeID = PatientDocumentType::getOtherId();
        //        if ($request->input('document_type_id') === $otherTypeID && isset($documentTypeID)) {
        //            $document->other_document_type = $request->input('other_document_type');
        //        }
        $supportingDocumentId = PatientDocumentType::getSupportingDocumentId();
        if ($document->documentType->parent_id == $supportingDocumentId) {
            $document->other_document_type = $document->documentType->type;
        }

        $document->save();

        $document->document_uploader = $this->getPatientDocumentUploader($document->id, class_basename($document));

        $dischargeIDs = PatientDocumentType::getFileTypeIDsLikeDischarge();
        $documentTypeID = $document->document_type_id;
        if (in_array($documentTypeID, $dischargeIDs)) {
            $patient = $document->patient;
            $dischargeStatusId = PatientStatus::getDischargedId();
            if ($patient->status_id != $dischargeStatusId) {
                $provider = TridiuumDischargeHelper::getProviderFromDischargeSummaryDocument($document);
                $this->discharge($patient, $provider);
            }
        }

        return response()->json($document);
    }

    /**
     * @param $id
     * Returns comment by comment ID
     *
     * @return mixed
     */
    public function getComment($id)
    {
        return PatientComment::findOrFail($id);
    }

    /**
     * @param $patientID
     * Returns patient comments by patient ID
     *
     * @return mixed
     */
    public function getComments($patientID)
    {
        return Patient::where('id', $patientID)->firstOrFail()->comments;
    }


    private function getImageMiniatureName($imageName, $width, $height)
    {
        $imageNameArr = explode('.', $imageName);
        $ext = array_pop($imageNameArr);
        return implode('.', $imageNameArr) . "_{$width}x{$height}." . $ext;
    }

    //    public function getImage40x40(Request $request) {
    //        $disk = Storage::disk('patients_docs');
    //        $imageName = $this->getImageMiniatureName($request->image, 40, 40);
    //        return "<img src='data:{$disk->mimeType($imageName)};base64," . base64_encode($disk->get($imageName)) . "'>";
    //    }

    public function ifExistsByOfficeAllyID(Request $request)
    {

        $id = $request->input('officeallyId');

        $patient = Patient::select('patient_id')->where('patient_id', $id)->first();
        if ($patient === null) {
            $exists = false;
        } else {
            $exists = true;
        }
        return response()->json([
            'exists' => $exists,
            'data' => $patient
        ]);
    }

    public function ifExistsByData(Request $request)
    {
        $patient = Patient::query()->select('patient_id')
            ->where('first_name', $request->input('firstName'))
            ->where('last_name', $request->input('lastName'))
            ->where('date_of_birth', \Carbon\Carbon::parse($request->input('dateOfBirth')))
            ->first();

        return response()->json([
            'exists' => !is_null($patient),
            'data' => $patient
        ]);
    }

    public function getPreviousNoteData($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $lastNote = $patient->patientNotes()
            ->where('is_finalized', true)
            ->where('provider_id', auth()->user()->provider_id)
            ->orderBy('created_at', 'desc')
            ->first();
        $responseData = [
            'diagnoses' => $patient->diagnoses()->active()->get(),
        ];
        if (!empty($lastNote)) {
            $responseData['long_range_treatment_goal'] = $lastNote->long_range_treatment_goal;
            $responseData['shortterm_behavioral_objective'] = $lastNote->shortterm_behavioral_objective;
        }

        return response([
            'data' => $responseData,
        ]);
    }

    public function hasInitialAssessmentForm($patientId)
    {
        $user = Auth::user();
        if ($user->isAdmin() || $user->isSecretary()) {
            return [
                'response' => true,
            ];
        }

        $assessmentIDs = AssessmentForm::getFileTypeIDsLikeInitialAssessment();
        $documentIDs = PatientDocumentType::getFileTypeIDsLikeInitialAssessment();
        $patient = Patient::select([
            'id'
        ])
            ->where('id', $patientId)
            ->withCount([
                'assessmentForms' => function ($query) use (&$assessmentIDs) {
                    $query->whereIn('assessment_form_id', $assessmentIDs);
                },
                'documents' => function ($query) use (&$documentIDs) {
                    $query->whereIn('document_type_id', $documentIDs);
                },
                'electronicDocuments' => function ($query) use (&$assessmentIDs) {
                    $query->whereIn('document_type_id', $assessmentIDs);
                },
                'patientNotes' => function ($query) {
                    $query->where('is_finalized', true);
                },
            ])
            ->firstOrFail();

        $response = [];
        $response['response'] = ($patient->assessment_forms_count > 0
            || $patient->documents_count > 0
            || $patient->patient_notes_count > 0
            || $patient->electronic_documents_count > 0);

        if (!$response['response']) {
            $initialAssessmentForms = AssessmentForm::initialAssessment()
                ->whereNotNull('file_name')
                ->get();
            $response['initial_assessment'] = $initialAssessmentForms;
        }

        return $response;
    }

    public function getFirstAppointmentDateHasntNote(Request $request)
    {
        $this->validate($request, [
            'patient_id' => 'required|numeric|exists:patients,id'
        ]);
        
        $patientId = $request->input('patient_id');

        if ($request->has('appointment_id')) {
            if (PatientNote::query()->where('appointment_id', $request->input('appointment_id'))->exists()) {
                return response()->json([
                    'message' => 'Progress Note already exists.',
                ], 400);
            }
            $appointment = Appointment::select([
                'appointments.id',
                'appointments.providers_id',
                'appointments.time',
                'appointments.treatment_modality_id',
                'appointment_statuses.status',
                'appointments.appointment_statuses_id'
            ])
                ->leftJoin('appointment_statuses', 'appointment_statuses.id', '=', 'appointments.appointment_statuses_id')
                ->where('appointments.patients_id', $patientId)
                ->where('appointments.id', $request->input('appointment_id'))
                ->first();
            if (!$appointment) {
                return response()->json([
                    'message' => 'Appointment is not exists.',
                ], 400);
            }
            if ($appointment->providers_id != auth()->user()->provider_id) {
                return response()->json([
                    'message' => 'Cannot create progress note for this appointment.',
                ], 400);
            }
            $date = Carbon::createFromTimestamp($appointment->time);
            $sessionTime = $this->getSessionTime($appointment);

            return [
                'date' => $date->format('m/d/Y'),
                'start_time' => $sessionTime['start_time'],
                'end_time' => $sessionTime['end_time'],
                'treatment_modality_id' => $appointment->treatment_modality_id,
                'formatted_dos' => sprintf('%s (%s)', $date->format('m/d/Y'), $appointment->status),
                'diagnoses_editable' => $appointment->appointment_statuses_id !== Status::getVisitCreatedId(),
            ];
        }

        $appointmentDate = Appointment::select('time')
            ->selectRaw("DATE(DATE_FORMAT(from_unixtime(appointments.time),'%Y-%m-%d')) as t")
            ->where('patients_id', '=', $patientId)
            ->where('note_on_paper', '=', false)
            ->onlyVisitCreated()
            ->havingRaw("t not in (SELECT DATE(date_of_service) FROM patient_notes where patient_notes.patients_id=appointments.patients_id and patient_notes.deleted_at is null)")
            ->orderBy('time')
            ->first();

        $date = is_null($appointmentDate) ? Carbon::now() : Carbon::createFromTimestamp($appointmentDate->time);

        return [
            'date' => $date->format('m/d/Y'),
            'start_time' => $date->format('g:i A'),
        ];
    }

    public function getDiagnosesDataset(Request $request, $id)
    {
        $diagnoses = PatientDiagnoseOld::find($id);
        if (empty($diagnoses)) {
            return [];
        }
        $diagnoses = $diagnoses->diagnose;
        $diagnoses = explode('","', $diagnoses);
        $diagnosesDataset = [];
        foreach ($diagnoses as $diagnose) {
            $tmp = trim($diagnose, '"');
            $diagnosesDataset[] = [
                'text' => $tmp,
                'value' => $tmp,
            ];
        }
        return $diagnosesDataset;
    }

    public function getDiagnosesCodesDataset(Request $request, $id)
    {
        $diagnoses = PatientDiagnoseOld::find($id);
        if (empty($diagnoses)) {
            return [];
        }
        $diagnoses = $diagnoses->diagnose;
        $diagnoses = explode('","', $diagnoses);
        $diagnosesDataset = [];
        foreach ($diagnoses as $diagnose) {
            preg_match('/\w\d{2,10}/', $diagnose, $icdCode);

            if (array_key_exists(0, $icdCode)) {
                $tmp = $icdCode[0];

                $diagnosesDataset[] = [
                    'text' => $tmp,
                    'value' => $tmp,
                ];
            }
        }
        return $diagnosesDataset;
    }

    public function getAppointmentDates(Request $request, $id)
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
        $visitCreatedId = Status::getVisitCreatedId();
        
        $appointments = Appointment::select([
            'appointments.id',
            'appointments.time',
            'appointments.treatment_modality_id',
            'appointments.appointment_statuses_id',
        ])
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('appointments.patients_id', $id)
            ->where('is_initial', 0)
            ->whereNull('appointments.initial_assessment_id')
            ->where('appointments.providers_id', Auth::user()->provider_id)
            ->where('appointments.note_on_paper', false)
            ->where('appointments.time', '<=', Carbon::now()->endOfDay()->timestamp)
            ->leftJoin('patient_notes', function (JoinClause $join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id');
                $join->whereNull($join->table . '.deleted_at');
            })
            ->whereNull('patient_notes.id')
            ->with([
                'status',
                'visit.diagnoses'
            ])
            ->orderByDesc('appointments.time')
            ->get();
            
        $appointmentsDataset = [];
        foreach ($appointments as $appointment) {
            $date = Carbon::createFromTimestamp($appointment->time);
            $sessionTime = $this->getSessionTime($appointment);

            $appointmentsDataset[] = [
                'text' => sprintf('%s (%s)', $date->format('m/d/Y'), optional($appointment->status)->status),
                'value' => $appointment->getKey(),
                'date' => $date->format('m/d/Y'),
                'start_time' => $sessionTime['start_time'],
                'end_time' => $sessionTime['end_time'],
                'treatment_modality_id' => $appointment->treatment_modality_id,
                'diagnoses' => optional($appointment->visit)->diagnoses ?? [],
                'diagnoses_editable' => $appointment->appointment_statuses_id !== $visitCreatedId
            ];
        }

        return $appointmentsDataset;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return array
     */
    public function getAppointmentDocumentDates(Request $request, $id)
    {
        return Appointment::statusNotCancel()
            ->select([
                'id',
                'time',
            ])
            ->with('visit.diagnoses')
            ->where('patients_id', $id)
            ->when($request->has('is_initial'), function ($query) use ($request) {
                $query
                    ->where('is_initial', $request->input('is_initial'))
                    ->whereNull('initial_assessment_id');
            })
            ->orderBy('time')
            ->get()
            ->transform(function (Appointment $appointment) {
                $date = Carbon::createFromTimestamp($appointment->time)->format('m/d/Y');

                return [
                    'text'  => $date,
                    'value' => $date,
                    'appointment_id' => $appointment->getKey(),
                    'diagnoses' => optional($appointment->visit)->diagnoses ?? []
                ];
            })->toArray();
    }

    /**
     * @deprecated to be deleted
     * @param $patientId
     * @param $appointmentId
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Symfony\Component\HttpFoundation\Response
     */
    public function getCompleteAppointmentData($patientId, $appointmentId)
    {
        $patient = Patient::where('patients.id', $patientId)
            ->select([
                'patients.id',
                'patients.insurance_plan_id',
                'patients.charge_for_cancellation_appointment AS pay_cost',
                'patients.email',
                'patients.secondary_email',
                'patients.cell_phone',
                'patients.visit_frequency_id',
                'first_name',
                'last_name',
                'middle_initial',
                'date_of_birth',
                'primary_insurance',
                DB::raw('patients.visit_copay / 100 AS co_pay'),
                'eff_start_date',
                'eff_stop_date',
                'visits_auth_left',
                'visits_auth',
                'is_self_pay',
                'self_pay',
                'deductible_remaining',
                'insurance_pay',
                'is_payment_forbidden',
                'appointments.id AS appointment_id',
                'appointments.payed AS appointment_payed_status',
                'appointments.reason_for_visit',
                'appointments.treatment_modality_id',
                'appointments.visit_length',
                'appointments.offices_id',
                'offices.office AS office_name',
                'appointments.office_room_id',
                'office_rooms.name AS office_room_name',
                'appointments.providers_id AS appointment_provider_id',
                'appointments.patients_id AS appointment_patient_id',
            ])->selectRaw("
                DATE_FORMAT(FROM_UNIXTIME(`appointments`.`time`), '%m/%d/%Y') AS `appointment_date`,TIME_FORMAT(FROM_UNIXTIME(`appointments`.`time`), '%h:%i %p') AS `appointment_time`
            ")
            ->join('appointments', 'appointments.patients_id', '=', 'patients.id')
            ->join('offices', 'appointments.offices_id', '=', 'offices.id')
            ->leftJoin('office_rooms', 'appointments.office_room_id', '=', 'office_rooms.id')

            ->with([
                'insurancePlan',
                'diagnoses',
                'squareAccount' => function ($query) {
                    $query->select([
                        'patient_id',
                        'id'
                    ]);
                    $query->with([
                        'cards' => function ($q) {
                            $q->select([
                                'last_four',
                                'patient_square_account_id',
                                'id',
                            ]);
                        },
                    ]);
                },
            ])
            ->where('appointments.id', $appointmentId)
            ->firstOrFail();

        $patient->co_pay = money_round($patient->co_pay);
        $patient->appointment_payed_status = intval($patient->appointment_payed_status);
        if (!is_null($patient->date_of_birth)) {
            $patient->years_old = Carbon::parse($patient->date_of_birth)->diffInYears();
        }

        $now = Carbon::now();
        $effStopDate = Carbon::parse($patient->eff_stop_date);
        $dateDiff = $now->diffInDays($effStopDate, false);
        $patient->is_eff_almost_overdue = isset($patient->insurancePlan) && $dateDiff <= $patient->insurancePlan->reauthorization_notification_days_count && $dateDiff >= 0;
        $patient->is_overdue = $dateDiff < 0;

        $appointment = Appointment::find($patient->appointment_id);
        $appointmentStartTime = null;
        $appointmentEndTime = null;

        $appointmentLogTime = $appointment->getTimeFromLogs();
        if (isset($appointmentLogTime)) {
            $appointmentStartTime = $appointmentLogTime['start_time'];
            $appointmentEndTime = $appointmentLogTime['end_time'];
        } else {
            $date = Carbon::createFromTimestamp($appointment->time);
            $appointmentStartTime = $date->format('g:i A');
            $appointmentEndTime = $date->format('g:i A');
        }

        $patient->appointment_start_time = $appointmentStartTime;
        $patient->appointment_end_time = $appointmentEndTime;

        return response($patient);
    }

    public function syncPatientWithOfficeAlly(SyncPatientWithOfficeAllyRequest $request)
    {
        $patient = Patient::where('patient_id', $request->patientOfficeAllyId)->first();
        $patient->update([
            'start_synchronization_time' => Carbon::now(),
        ]);
        $job = (new SinglePatientDataParser($patient))->onQueue('single-parser');
        dispatch($job);

        return response([]);
    }

    public function addCreditCard(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        $squareAccount = $patient->squareAccount;

        if ($squareAccount == null) {
            if ($patient->informationForm == null) {
                $validator = Validator::make($request->toArray(), [
                    'email' => 'required|email'
                ]);
                if ($validator->fails()) {
                    return response(['errors' => $validator->errors()], 400);
                }
                $patient->informationForm()->create([
                    'email' => $request->get('email'),
                    'zip'   => $request->get('cardData')['billing_postal_code'],
                ]);
            } else {
                $patient->informationForm()->update([
                    'zip'   => $request->get('cardData')['billing_postal_code'],
                ]);
            }
            $squareCustomer = new Customer();
            $squareAccount = $squareCustomer->createIfNotExist($patient, [
                'email' => $request->input('email'),
            ]);
        }

        $patient = Patient::findOrFail($id);
        $patient->append('square_account');

        $squareCustomerCardService = new CustomerCard();
        try {
            $squareCustomerCardService->create($squareAccount, $request->get('nonce'), $request->get('cardData')['billing_postal_code']);
        } catch (SquareException $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return response()->json([
                'success' => false,
                'errors' => $e->getErrors(),
            ], 400);
        }

        $patient = Patient::findOrFail($id);
        $patient->load('informationForm', 'squareAccount', 'squareAccount.cards');

        return response($patient);
    }

    public function getPatientsForSquare(GetForSquareRequest $request)
    {
        $limit = $request->get('limit') ?? 10;
        $q = $request->get('q');
        $patients = Patient::query()
            ->select([
                \DB::raw("CONCAT(first_name, ' ', last_name) AS label"),
                'id AS value'
            ])
            ->when(!empty($q), function ($query) use ($q) {
                $query->whereRaw("(CONCAT(first_name, ' ', last_name) LIKE '%{$q}%' OR id LIKE '%{$q}%' OR patient_id LIKE '%{$q}%')");
            })
            ->orderBy('label')
            ->limit($limit)
            ->get();

        return response()->json([
            'patients' => $patients,
        ]);
    }

    public function update(UpdateRequest $request, Patient $patient)
    {
        $dataForUpdateInOA = [];
        $credentials = $request->credentials();
        if (array_key_exists('cell_phone', $credentials)) {
            $dataForUpdateInOA['cell_phone'] = split_phone($credentials['cell_phone']);
        }
        if (array_key_exists('home_phone', $credentials)) {
            $dataForUpdateInOA['home_phone'] = split_phone($credentials['home_phone']);
        }
        if (array_key_exists('work_phone', $credentials)) {
            $dataForUpdateInOA['work_phone'] = split_phone($credentials['work_phone']);
        }
        if (array_key_exists('email', $credentials)) {
            $dataForUpdateInOA['email'] = $credentials['email'];
        }
        if (!empty($dataForUpdateInOA)) {
            RetryJobQueueHelper::dispatchRetryUpdatePatient(Option::OA_ACCOUNT_1, $dataForUpdateInOA, $patient->id);
        }
        if (!auth()->user()->isAdmin()) {
            $credentials = array_only($credentials, [
                'cell_phone',
                'cell_phone_label',
                'work_phone',
                'work_phone_label',
                'home_phone',
                'home_phone_label',
                'email',
            ]);
        }
        if (!empty($credentials)) {
            $patient->update($credentials);
        }

        if ($request->hasAdditionalPhones()) {
            $phoneType = $request->get('additional_phones_phone_type');
            $additionalPhones = $request->get('additional_phones');

            $existingAdditionalPhonesIds = [];

            foreach ($additionalPhones as $additionalPhone) {
                if (isset($additionalPhone['id'])) {
                    $existingAdditionalPhonesIds[] = $additionalPhone['id'];
                }
            }

            $additionalPhonesIdsToDelete = $patient->additionalPhones()
                ->where('phone_type', $phoneType)
                ->whereNotIn('id', $existingAdditionalPhonesIds)
                ->pluck('id');

            $patient->additionalPhones()->whereIn('id', $additionalPhonesIdsToDelete)->delete();

            foreach ($additionalPhones as $additionalPhone) {
                $payload = [
                    'phone' => sanitize_phone($additionalPhone['phone']),
                    'label' => $additionalPhone['label'],
                    'phone_type' => $phoneType,
                ];

                if (isset($additionalPhone['id'])) {
                    $patient->additionalPhones()->where('id', $additionalPhone['id'])->update($payload);
                } else {
                    $patient->additionalPhones()->create($payload);
                }
            }
        }

        if ($request->hasArchiveAction() && auth()->user()->isAdmin()) {
            $patientTransfer = $patient->transfers()
                ->active()
                ->first();

            if ($patientTransfer) {
                $patientTransfer->close();
            }

            $patient->detachTag(PatientTag::getTransferringId());

            $userMeta = \Auth::user()->meta;

            $comment = $request->comment
                ? trans('comments.admin_was_archive_patient_with_comment', [
                    'user_name' => optional($userMeta)->getFullname() ?? '',
                    'comment' => $request->comment,
                ])
                : trans('comments.admin_was_archive_patient', [
                    'user_name' => optional($userMeta)->getFullname() ?? '',
                ]);

            event(new NeedsWriteSystemComment($patient->id, $comment));
        }

        return response(['patient' => $patient]);
    }
    
    public function getPatientNotesWithDocumentsCount($patientId)
    {
        return response()->json($this->patientRepository->patientNotesWithDocumentsCount($patientId));
    }

    public function getPatientNoteAndAppointmentCount(Patient $patient) 
    {
        return [
            'patient_note_count' => $this->getPatientNoteCount($patient),
            'on_paper_count' => Patient::getPnCoefficient($patient->id),
            'draft_patient_note_count' => $this->getPatientDraftProgressNoteCount($patient),
            'missing_patient_note_count' => $this->getMissingProgressNoteCount($patient),
            'initial_assessment_count' => $this->getPatientInitialAssessmentCount($patient),
            'appointment_count' => $patient->appointments()->count(),
            'appointment_visit_created_count' => $this->getPatientAppointmentVisitCreatedCount($patient),
            'appointment_completed_count' => $this->getPatientAppointmentCompletedCount($patient),
            'google_meeting_appointment_count' => $this->getPatientGoogleMeetingsAppointmentCount($patient->id),
            'ring_central_appointment_count' => $this->getPatientRingCentralAppointmentCount($patient->id),
            'cancelled_appointments' => $this->getPatientCancelledAppointments($patient),
            'visit_average_duration' => $this->getVisitAverageDuration($patient->id), 
        ];
    }

    public function getVisitFrequenciesList() {
        return PatientVisitFrequency::all();
    }
}
