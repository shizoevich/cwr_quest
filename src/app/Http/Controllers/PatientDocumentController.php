<?php

namespace App\Http\Controllers;

use App\AssessmentForm;
use App\Events\PatientDocumentDownloaded;
use App\Events\PatientDocumentSent as PatientDocumentSentEvent;
use App\Events\PatientDocumentStatusChanged;
use App\Http\Requests\Dashboard\DocumentToSend\DischargeSummary;
use App\Http\Requests\Dashboard\DocumentToSend\InitialAssessment;
use App\Http\Requests\Dashboard\DocumentToSend\ReauthorizationRequests;
use App\Http\Requests\PatientDocuments\ChangeDocumentStatus;
use App\Jobs\Comments\ParseCommentMentions;
use App\Jobs\Documents\PrepareAssessmentDownload;
use App\Jobs\Documents\PrepareDocumentDownload;
use App\Jobs\Documents\PrepareElectronicDocumentDownload;
use App\Jobs\Documents\PrepareNoteDownload;
use App\Jobs\Fax\SendDocumentFax;
use App\Jobs\Database\StoreDocumentSendInfo;
use App\Mail\Patient\DocumentDownloadRequest;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientAssessmentForm;
use App\PatientDocumentShared;
use App\PatientDocument;
use App\PatientDocumentComment;
use App\PatientDocumentType;
use App\PatientNote;
use App\Provider;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use App\SentDocument;
use App\SharedDocumentStatus;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PatientDocumentController extends Controller
{
    private function makeUrlHash()
    {
        return \Uuid::generate()->string;
    }

    private function makeDocumentUrl($shared_link)
    {
        return route('document-download.index',
            ['documentUrl' => $shared_link]);
    }

    private function getProvider($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return null;
        }

        return $provider;
    }

    public function storeDocumentComment(Request $request)
    {
        $this->validate($request, [
            'patient_documents_id' => 'required|numeric',
            'content'              => 'required',
        ]);

        $data = $request->all(); 

        if (empty($data['provider_id'])) {
            $user = Auth::user();
            unset($data['provider_id']);
            if ($user->isAdmin()) {
                $data['admin_id'] = $user->id;
            }
        }

        if ($data['document_model'] == 'PatientElectronicDocument') {
            $data['document_model'] = PatientElectronicDocument::class;
        } else {
            $data['document_model'] = 'App\\' . $data['document_model'];
        }
        $data['content'] = strip_tags($data['content'], '<span>');
        $comment = PatientDocumentComment::create($data);
        if (!empty($comment)) {
            if ($data['document_model'] === 'App\PatientDocument') {
                $document = $data['document_model']::withoutGlobalScope(DocumentsForAllScope::class)
                    ->where('id', $data['patient_documents_id'])
                    ->first();
            } else {
                $document = $data['document_model']::find($data['patient_documents_id']);
            }
            $patientId = $document->patient()->select('id')->first()['id'];
            \Bus::dispatchNow(new ParseCommentMentions($data['content'], $comment->id, 'PatientDocumentComment', $patientId));
        }

        return response()->json([
            'success'  => true,
            'comment'  => $comment,
            'user'     => $comment->user,
            'provider' => $comment->provider,
        ], 201);
    }

    public function sendMail(Request $request)
    {
        $user = null;
        $provider = null;
        $data = $request->all();
        $sender = new \SplObjectStorage();
        $shared_link = $this->makeUrlHash();

        if (empty($data['provider_id'])) {
            $user = Auth::user();
            $firstName = $user->firstname ?? 'Administrator';
            $lastName = $user->lastname ?? '';
            $sender->name = implode(' ', [$firstName, $lastName]);
        } else {
            $provider = $this->getProvider($data['provider_id']);
            $sender->name = $provider->provider_name;
        }

        \Mail::to($request->recipient)->send(new DocumentDownloadRequest($this->makeDocumentUrl($shared_link)));

        $sharedDocument = $this->dispatchNow(new StoreDocumentSendInfo(array_merge($request->all(),
            [
                'provider'    => $provider,
                'user'        => $user,
                'shared_link' => $shared_link,
                // 'external_id' => $mandrillResponse[0]['_id'],
            ]))
        );

        event(new PatientDocumentSentEvent($sharedDocument));
    }


    public function sendFax(Request $request)
    {
        // throw new \Exception('Sending a fax is now disabled.');
        $user = null;
        $provider = null;
        $data = $request->all();
        $sender = new \SplObjectStorage();
        $shared_link = $this->makeUrlHash();

        if (empty($data['provider_id'])) {
            $user = Auth::user();
            $firstName = isset($user->firstname) ? $user->firstname : 'Administrator';
            $lastName = isset($user->lastname) ? $user->lastname : '';
            $sender->name = implode(' ', [$firstName, $lastName]);
        } else {
            $provider = $this->getProvider($data['provider_id']);
            $sender->name = $provider->provider_name;
        }

        $external_id = $this->dispatchNow(new SendDocumentFax($request->recipient, $data));

        if ($external_id === -1) {
            return response(__('download.max_size'), 403);
        } 
        if ($external_id === null) {
            return response(__('download.no_document'), 500);
        }

        $sharedDocument = $this->dispatchNow(new StoreDocumentSendInfo(array_merge($request->all(),
            [
                'provider'             => $provider,
                'user'                 => $user,
                'shared_link'          => $shared_link,
                'shared_link_password' => null,
                'external_id'          => $external_id,
            ]))
        );

        event(new PatientDocumentSentEvent($sharedDocument));
    }

    public function index(Request $request, $shared_link)
    {
        if ($shared_link) {
            $sharedDocument = PatientDocumentShared::query()
                ->where('shared_link', '=', $shared_link)
                ->with(['documentDownloadInfo', 'documentSharedLog'])
                ->first();
        } else {
            $errors = [
                'document' => __('download.no_document'),
            ];

            return view('document')->withErrors($errors);
        }

        if ($this->isDocumentEmpty($sharedDocument)) {
            $errors = [
                'document' => __('download.no_document'),
            ];

            return view('document')->withErrors($errors)
                ->withInput($request->input());
        }

        $downloadAttempts = $sharedDocument->documentDownloadInfo->count();
        $downloadAttemptsLeft = config('shared_document.max_download_attempts')
            - $downloadAttempts;

        if ($downloadAttemptsLeft <= 0) {
            $errors = [
                'document' => __('download.no_download_attempts'),
            ];

            return view('document')->withErrors($errors);
        }

        $createDate = $sharedDocument->documentSharedLog->created_at;
        $now = Carbon::now();
        $createdDateCopy = $createDate->copy()
            ->addDays(config('shared_document.shared_link_life_days'));
        $daysLeft = $now->diffInDays($createdDateCopy, false) + 1;

        if ($daysLeft <= 0) {
            $errors = [
                'document' => __('download.link_lifetime_exceeded', ['number' => config('shared_document.shared_link_life_days')]),
            ];

            return view('document')->withErrors($errors);
        }

        return view('document', compact([
            'shared_link',
            'downloadAttemptsLeft',
            'daysLeft',
        ]));
    }


    public function downloadDocument(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
        ]);

        $sharedDocument = PatientDocumentShared::where('shared_link', '=',
            $request->shared_link)->first();

        if ($this->isDocumentEmpty($sharedDocument)) {
            $errors = [
                'document' => __('download.no_document'),
            ];

            return redirect()->back()->withErrors($errors);
//            return view('document')->withErrors($errors)
//                ->withInput($request->input());
        }

        if ($this->isPasswordInvalid($request, $sharedDocument)) {
            $errors = [
                'password' => __('download.wrong_password'),
            ];

            return redirect()->back()->withErrors($errors);
//            return view('document')->withErrors($errors)
//                ->withInput($request->input());
        }

        $downloadAttempts = $sharedDocument->documentDownloadInfo->count();
        $downloadAttemptsLeft = config('shared_document.max_download_attempts')
            - $downloadAttempts;

        if ($downloadAttemptsLeft <= 0) {
            $errors = [
                'document' => __('download.no_download_attempts'),
            ];

            return back()->withErrors($errors);
        }

        $createDate = $sharedDocument->documentSharedLog->created_at;
        $now = Carbon::now();
        $createdDateCopy = $createDate->copy()
            ->addDays(config('shared_document.shared_link_life_days'));
        $daysLeft = $now->diffInDays($createdDateCopy, false) + 1;

        if ($daysLeft <= 0) {
            $errors = [
                'document' => __('download.link_lifetime_exceeded', ['number' => config('shared_document.shared_link_life_days')]),
            ];

            return back()->withErrors($errors);
        }

        switch ($sharedDocument->document_model) {
            case PatientNote::class:
                $preparedData
                    = \Bus::dispatchNow(new PrepareNoteDownload($sharedDocument->patient_documents_id));
                break;
            case PatientDocument::class:
                $preparedData
                    = \Bus::dispatchNow(new PrepareDocumentDownload($sharedDocument->patient_documents_id));
                break;
            case PatientAssessmentForm::class:
                $preparedData
                    = \Bus::dispatchNow(new PrepareAssessmentDownload($sharedDocument->patient_documents_id));
                break;
            case PatientElectronicDocument::class:
                $preparedData
                    = \Bus::dispatchNow(new PrepareElectronicDocumentDownload($sharedDocument->patient_documents_id));
                break;
            default:
                return response('Sorry, there is no docs', 500);
                break;
        }

        if ($preparedData === null) {
            $errors = [
                'document' => __('download.no_document'),
            ];

            return redirect()->back()->withErrors($errors);
//            return view('document')->withErrors($errors);
        } else {

            $file = $preparedData['file'];
            $mime = $preparedData['mime'];
            $documentName = $preparedData['documentName'];

            event(new PatientDocumentDownloaded($sharedDocument));

            return response($file, 200, [
                "Content-Type"        => $mime,
                "Content-disposition" => "attachment; filename=\"" . $documentName . "\"",
            ]);

        }
    }

    public function downloadSuccess()
    {
        return view('download_success');
    }

    protected function isDocumentEmpty($sharedDocument)
    {
        if(is_null($sharedDocument)) {
            return true;
        }
        $model = $sharedDocument->document_model;
        $id = $sharedDocument->patient_documents_id;
        $document = $model::find($id);

        return empty($document);
    }

    protected function isPasswordInvalid($request, $sharedDocument)
    {
        if (!Hash::check($request->password,
            $sharedDocument->shared_link_password)) {
            return true;
        } else {
            return false;
        }
    }

    private function getCreatorName(&$doc)
    {
        $creatorName = "-";
        if (is_null($doc->provider_id) && !is_null($doc->admin_id)) {
            $user = UserMeta::withTrashed()
                ->select([
                    'firstname',
                    'lastname',
                ])
                ->where('user_id', $doc->admin_id)
                ->first();
            if (!is_null($user) && !is_null($user->firstname) && !is_null($user->lastname)) {
                $creatorName = "{$user->firstname} {$user->lastname}";
            }
        } else if (!is_null($doc->provider_id)) {
            $provider = Provider::withTrashed()->where('id', $doc->provider_id)->first();
            if (!is_null($provider)) {
                $creatorName = $provider->provider_name;
            }
        }
        $doc->creator_name = $creatorName;
    }

    private function getAdditionalSentInfo(&$doc, &$ajaxResponseData, &$request)
    {
        $document = ($doc->document_model)::withTrashed()->where('id', $doc->patient_documents_id)->first();
        if (!is_null($document)) {
            switch ($doc->document_model) {
                case PatientNote::class:
                    $doc->document_name = "Progress Note";
                    break;
                case PatientAssessmentForm::class:
                    $doc->document_name = $document->assessmentFormTemplate->title;
                    break;
                case PatientDocument::class:
                    $doc->document_name = $document->original_document_name;
                    break;
                default:
                    $doc->document_name = 'n/a';
            }


            $patient = $document->patient;
            if (!is_null($patient)) {
                $doc->patient_name = $patient->first_name . ' ' . $patient->last_name .
                    (!is_null($patient->middle_initial) ? (' ' . $patient->middle_initial) : '');
                $doc->patient_id = $patient->id;
            }
        }
        $doc->sent_date = ($doc->created_at)->format('m/d/Y h:i A');
        $doc->sent_date_timestamp = ($doc->created_at)->timestamp;
        if ($request->has('providerId')) {
            if ($request->providerId == -1) {
                $ajaxResponseData[] = $doc;
            } else if (!is_null($doc->provider_id)) {
                if ($doc->provider_id == $request->providerId) {
                    $ajaxResponseData[] = $doc;
                }
            } else if (!is_null($doc->admin_id) && $request->providerId == -7777) {
                $ajaxResponseData[] = $doc;
            }
        } else {
            $ajaxResponseData[] = $doc;
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getSentDocumentsByEmailStatistic(Request $request)
    {
        $sentStatusId = null;
        if ($request->has('sentStatusId') && $request->sentStatusId > 0) {
            $sentStatusId = $request->sentStatusId;
        }
        $documentSharedEmails = PatientDocumentShared::select([
            'id',
            'patient_documents_id',
            'document_model',
            'recipient',
            'provider_id',
            'admin_id',
            'created_at',
        ])
            ->onlyEmailMethod()
            ->withCount([
                'documentDownloadInfo AS download',
                'documentSharedLog AS shared' => function ($query) use ($sentStatusId) {
                    if (!is_null($sentStatusId)) {
                        $query->where('shared_document_statuses_id', $sentStatusId);
                    }
                },
            ])
            ->with([
                'documentSharedLog' => function ($query) {
                    $query->select([
                        'patient_document_shared_id',
                        'shared_document_statuses_id',
                    ]);
                    $query->with([
                        'sharedStatus',
                    ]);
                },
            ])
            ->havingRaw('shared > 0')
            ->orderBy('created_at', 'desc')
            ->get();

        $ajaxResponseData = [];
        foreach ($documentSharedEmails as $doc) {
            $this->getCreatorName($doc);
            $this->getAdditionalSentInfo($doc, $ajaxResponseData, $request);
        }

        return $ajaxResponseData;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getSentDocumentsByFaxStatistic(Request $request)
    {
        $ajaxResponseData = [];
        $documentSharedFax = PatientDocumentShared::select([
            'id',
            'patient_documents_id',
            'document_model',
            'recipient',
            'provider_id',
            'admin_id',
            'created_at',
            'shared_document_methods_id',
        ])
            ->onlyFaxMethod()
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($documentSharedFax as $doc) {
            $this->getCreatorName($doc);
            $this->getAdditionalSentInfo($doc, $ajaxResponseData, $request);
        }

        return $ajaxResponseData;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSentDocumentsStatistic()
    {
        $documentSharedEmails = $this->getSentDocumentsByEmailStatistic(new Request());
        $documentSharedFaxes = $this->getSentDocumentsByFaxStatistic(new Request());
        $doctors = Provider::orderBy('provider_name')
            ->select(['id', 'provider_name'])
            ->get();
        $sentStatuses = SharedDocumentStatus::orderBy('status')->get();

        return view('dashboard.statistics.sent-documents-statistic', compact(
            'documentSharedEmails',
            'documentSharedFaxes',
            'doctors',
            'sentStatuses'
        ));
    }

    private function getDocumentsToSend(array $filters, array $documentTypeIds, array $assessmentFormTypeIds, int $page = 1): LengthAwarePaginator
    {
        $sent = (int)array_get($filters, 'sent', 1);
        $toSend = (int)array_get($filters, 'to_send', 1);
        $approved = (int)array_get($filters, 'approved', 1);
        $statusFilter = [
            'sent' => $sent && !$toSend && !$approved,
            'to_send' => !$sent && $toSend && !$approved,
            'approved' => !$sent && !$toSend && $approved,
            'sent_to_send' => $sent && $toSend && !$approved,
            'sent_approved' => $sent && !$toSend && $approved,
            'to_send_approved' => !$sent && $toSend && $approved,
            'sent_to_send_approved' => $sent && $toSend && $approved,
        ];
        $perPage = 15;
        $patientDocuments = PatientDocument::select([
            'patient_documents.id AS document_id',
            'patients.id AS patient_id',
            DB::raw("'PatientDocument' AS document_model"),
            DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"),
            'original_document_name AS document_name',
            'patient_documents.document_type_id',
            'patient_documents.created_at',
            DB::raw("IF(providers.provider_name IS NULL, CONCAT(users_meta.firstname, ' ', users_meta.lastname), providers.provider_name) AS provider_name"),
            DB::raw("IF(providers.id IS NULL, '-7777', providers.id) AS provider_id"),
            'aws_document_name',
            DB::raw("IF(patient_document_shared.recipient IS NULL, 0, 1) AS sent"),
            DB::raw("IF(sent_documents.is_sent IS NULL, 0, sent_documents.is_sent) AS custom_sent"),
            DB::raw("IF(patient_document_shared.recipient IS NOT NULL OR sent_documents.is_sent=1, 1, 0) AS global_sent"),
            DB::raw("IF(sent_documents.approved_at IS NULL, 0, 1) AS is_approved"),
            'sent_documents.approved_at AS approved',
        ])
            ->whereIn('document_type_id', $documentTypeIds)
            ->join('patients', 'patients.id', '=', 'patient_documents.patient_id')
            ->leftJoin('patient_document_upload_info', function ($join) {
                $join->on('patient_document_upload_info.patient_document_id', '=', 'patient_documents.id')
                    ->on('patient_document_upload_info.document_model', '=', DB::raw("'App\\\\PatientDocument'"));
            })
            ->leftJoin('users', 'users.id', '=', 'patient_document_upload_info.user_id')
            ->leftJoin('users_meta', 'users_meta.user_id', '=', 'users.id')
            ->leftJoin('providers', 'users.provider_id', '=', 'providers.id')
            ->leftJoin('patient_document_shared', function ($join) {
                $join->on('patient_document_shared.patient_documents_id', '=', 'patient_documents.id')
                    ->on('patient_document_shared.document_model', '=', DB::raw("'App\\\\PatientDocument'"));
            })
            ->leftJoin('sent_documents', function ($join) {
                $join->on('sent_documents.document_id', '=', 'patient_documents.id')
                    ->on('sent_documents.document_model', '=', DB::raw("'PatientDocument'"));
            })->when((int)array_get($filters, 'provider_id', 0) > 0, function($query) use ($filters) {
                $query->having('provider_id', $filters['provider_id']);
            })->when(!$statusFilter['sent_to_send_approved'], function($query) use ($statusFilter) {
                if($statusFilter['sent']) {
                    $query->having('is_approved', '=', 0)->having('global_sent', '=', 1);
                } else if($statusFilter['to_send']) {
                    $query->having('global_sent', 0)->having('is_approved', '=', 0);
                } else if($statusFilter['approved']) {
                    $query->having('is_approved', '=', 1);
                } else if($statusFilter['sent_to_send']) {
                    $query->having('is_approved', '=', 0);
                } else if($statusFilter['sent_approved']) {
                    $query->having('global_sent', 1)->orHaving('is_approved', '=', 1);
                } else if($statusFilter['to_send_approved']) {
                    $query->having('global_sent', 0)->orHaving('is_approved', '=', 1);
                }
            })->when(!empty(array_get($filters, 'date')), function($query) use ($filters) {
                $query->whereDate('patient_documents.created_at', '=', $filters['date']);
            });

        $assessmentForms = PatientAssessmentForm::select([
            'patients_assessment_forms.id AS document_id',
            'patients.id AS patient_id',
            DB::raw("'PatientAssessmentForm' AS document_model"),
            DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"),
            'assessment_forms.title AS document_name',
            'patients_assessment_forms.assessment_form_id AS document_type_id',
            'patients_assessment_forms.created_at',
            'providers.provider_name',
            'providers.id AS provider_id',
            DB::raw("CONCAT('urn:oid:', s3_file_id) AS aws_document_name"),
            DB::raw("IF(patient_document_shared.recipient IS NULL, 0, 1) AS sent"),
            DB::raw("IF(sent_documents.is_sent IS NULL, 0, sent_documents.is_sent) AS custom_sent"),
            DB::raw("IF(patient_document_shared.recipient IS NOT NULL OR sent_documents.is_sent=1, 1, 0) AS global_sent"),
            DB::raw("IF(sent_documents.approved_at IS NULL, 0, 1) AS is_approved"),
            'approved_at AS approved',
        ])
            ->whereIn('patients_assessment_forms.assessment_form_id', $assessmentFormTypeIds)
            ->join('users', 'users.id', '=', 'patients_assessment_forms.creator_id')
            ->join('providers', 'users.provider_id', '=', 'providers.id')
            ->join('patients', 'patients.id', '=', 'patients_assessment_forms.patient_id')
            ->join('assessment_forms', 'assessment_forms.id', '=', 'patients_assessment_forms.assessment_form_id')
            ->leftJoin('patient_document_shared', function ($join) {
                $join->on('patient_document_shared.patient_documents_id', '=', 'patients_assessment_forms.id')
                    ->on('patient_document_shared.document_model', '=', DB::raw("'App\\\\PatientAssessmentForm'"));
            })
            ->leftJoin('sent_documents', function ($join) {
                $join->on('sent_documents.document_id', '=', 'patients_assessment_forms.id')
                    ->on('sent_documents.document_model', '=', DB::raw("'PatientAssessmentForm'"));
            })->when((int)array_get($filters, 'provider_id', 0) > 0, function($query) use ($filters) {
                $query->having('provider_id', $filters['provider_id']);
            })->when(!$statusFilter['sent_to_send_approved'], function($query) use ($statusFilter) {
                if($statusFilter['sent']) {
                    $query->having('is_approved', '=', 0)->having('global_sent', '=', 1);
                } else if($statusFilter['to_send']) {
                    $query->having('global_sent', 0)->having('is_approved', '=', 0);
                } else if($statusFilter['approved']) {
                    $query->having('is_approved', '=', 1);
                } else if($statusFilter['sent_to_send']) {
                    $query->having('is_approved', '=', 0);
                } else if($statusFilter['sent_approved']) {
                    $query->having('global_sent', 1)->orHaving('is_approved', '=', 1);
                } else if($statusFilter['to_send_approved']) {
                    $query->having('global_sent', 0)->orHaving('is_approved', '=', 1);
                }
            })->when(!empty(array_get($filters, 'date')), function($query) use ($filters) {
                $query->whereDate('patients_assessment_forms.created_at', '=', $filters['date']);
            });

        $patientElectronicDocuments = PatientElectronicDocument::query()->select([
            'patient_electronic_documents.id AS document_id',
            'patients.id AS patient_id',
            DB::raw("'PatientElectronicDocument' AS document_model"),
            DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"),
            'assessment_forms.title AS document_name',
            'patient_electronic_documents.document_type_id AS document_type_id',
            'patient_electronic_documents.created_at',
            'providers.provider_name',
            'providers.id AS provider_id',
            DB::raw("CONCAT(patient_electronic_documents.id, '.docx') AS aws_document_name"),
            DB::raw("IF(patient_document_shared.recipient IS NULL, 0, 1) AS sent"),
            DB::raw("IF(sent_documents.is_sent IS NULL, 0, sent_documents.is_sent) AS custom_sent"),
            DB::raw("IF(patient_document_shared.recipient IS NOT NULL OR sent_documents.is_sent=1, 1, 0) AS global_sent"),
            DB::raw("IF(sent_documents.approved_at IS NULL, 0, 1) AS is_approved"),
            'approved_at AS approved',
        ])->whereIn('patient_electronic_documents.document_type_id', $assessmentFormTypeIds)
            ->join('providers', 'patient_electronic_documents.provider_id', '=', 'providers.id')
            ->join('patients', 'patients.id', '=', 'patient_electronic_documents.patient_id')
            ->join('assessment_forms', 'assessment_forms.id', '=', 'patient_electronic_documents.document_type_id')
            ->leftJoin('patient_document_shared', function ($join) {
                $join->on('patient_document_shared.patient_documents_id', '=', 'patient_electronic_documents.id')
                    ->on('patient_document_shared.document_model', '=', DB::raw("'App\\\\Models\\\\Patient\\\\PatientElectronicDocument'"));
            })->leftJoin('sent_documents', function ($join) {
                $join->on('sent_documents.document_id', '=', 'patient_electronic_documents.id')
                    ->on('sent_documents.document_model', '=', DB::raw("'PatientElectronicDocument'"));
            })->when((int)array_get($filters, 'provider_id', 0) > 0, function($query) use ($filters) {
                $query->where('providers.id', $filters['provider_id']);
            })->when(!$statusFilter['sent_to_send_approved'], function($query) use ($statusFilter) {
                if($statusFilter['sent']) {
                    $query->having('is_approved', '=', 0)->having('global_sent', '=', 1);
                } else if($statusFilter['to_send']) {
                    $query->having('global_sent', 0)->having('is_approved', '=', 0);
                } else if($statusFilter['approved']) {
                    $query->having('is_approved', '=', 1);
                } else if($statusFilter['sent_to_send']) {
                    $query->having('is_approved', '=', 0);
                } else if($statusFilter['sent_approved']) {
                    $query->having('global_sent', 1)->orHaving('is_approved', '=', 1);
                } else if($statusFilter['to_send_approved']) {
                    $query->having('global_sent', 0)->orHaving('is_approved', '=', 1);
                }
            })->when(!empty(array_get($filters, 'date')), function($query) use ($filters) {
                $query->whereDate('patient_electronic_documents.created_at', '=', $filters['date']);
            });

        $query = $patientDocuments->union($assessmentForms)
            ->union($patientElectronicDocuments);
        $count = \DB::select(sprintf("
          SELECT COUNT(*) AS total_count, COUNT(IF(tmp.sent = 1 OR tmp.custom_sent = 1, 1, NULL)) AS sent_count 
          FROM (%s) AS tmp
        ", $query->toSql()), $query->getBindings());
        $total = data_get($count, '0.total_count');
        $sentCount = data_get($count, '0.sent_count');

        $documents = $query->orderByDesc('created_at')
            ->take($perPage)
            ->skip($perPage * ($page - 1))
            ->get();

        return new \Illuminate\Pagination\LengthAwarePaginator([
            'data' => $documents,
            'sent_count' => $sentCount,
        ], $total, $perPage, $page);
    }

    public function getDocumentsToSendReauthorizationRequests(ReauthorizationRequests $request)
    {
        $rrIds = PatientDocumentType::getFileTypeIDsLikeReauthorization();
        $assessmentRrIds = AssessmentForm::getFileTypeIDsLikeReauthorization();
        $page = $request->get('page');
        if((int)$page <= 0) {
            $page = 1;
        }
        $filters = [
            'provider_id' => $request->get('provider_id'),
            'sent' => $request->get('sent'),
            'to_send' => $request->get('to_send'),
            'approved' => $request->get('approved'),
            'date' => $request->get('date'),
        ];
        $documents = $this->getDocumentsToSend($filters, $rrIds, $assessmentRrIds, $page);

        $dataset = $this->getDocumentsToSendDataset($documents);

        return response($dataset);
    }

    public function getDocumentsToSendDischargeSummary(DischargeSummary $request)
    {
        $dischargeIds = PatientDocumentType::getFileTypeIDsLikeDischarge();
        $assessmentDischargeIds = AssessmentForm::getFileTypeIDsLikeDischarge();
        $page = $request->get('page');
        if((int)$page <= 0) {
            $page = 1;
        }
        $filters = [
            'provider_id' => $request->get('provider_id'),
            'sent' => $request->get('sent'),
            'to_send' => $request->get('to_send'),
            'approved' => $request->get('approved'),
            'date' => $request->get('date'),
        ];
        $documents = $this->getDocumentsToSend($filters, $dischargeIds, $assessmentDischargeIds, $page);

        $dataset = $this->getDocumentsToSendDataset($documents);

        return response($dataset);
    }

    public function getDocumentsToSendInitialAssessment(InitialAssessment $request)
    {
        $initialAssessmentIds = PatientDocumentType::getFileTypeIDsLikeInitialAssessment();
        $assessmentInitialIds = AssessmentForm::getFileTypeIDsLikeInitialAssessment();
        $page = $request->get('page');
        if((int)$page <= 0) {
            $page = 1;
        }
        $filters = [
            'provider_id' => $request->get('provider_id'),
            'sent' => $request->get('sent'),
            'to_send' => $request->get('to_send'),
            'approved' => $request->get('approved'),
            'date' => $request->get('date'),
        ];
        $documents = $this->getDocumentsToSend($filters, $initialAssessmentIds, $assessmentInitialIds, $page);

        $dataset = $this->getDocumentsToSendDataset($documents);

        return response($dataset);
    }

    /**
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     *
     * @return array
     */
    private function getDocumentsToSendDataset(LengthAwarePaginator $paginator): array
    {
        $collection = $paginator->getCollection();
        $documents = $collection['data'];
        $dataset = [];
        foreach ($documents as $doc) {
            $date = $doc->created_at->format('m/d/Y');
            $doc->default_address = $this->getPatientDocumentDefaultAddresses($doc);
            $doc->sent = intval($doc->sent);
            $doc->custom_sent = intval($doc->custom_sent);
            if (!is_null($doc->approved)) {
                $doc->approved = Carbon::parse($doc->approved)->format('m/d/Y h:i A');
            }
            $dataset[$date]['dataset'][] = $doc->toArray();
            $dataset[$date]['date'] = $date;
        }

        return [
            'data' => $dataset,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'sent_count' => $collection['sent_count'],
        ];
    }

    public static function getDocumentsToSendCount()
    {
        if (!Schema::hasTable('sent_documents')) {
            return 0;
        }
        $rrIds = PatientDocumentType::getFileTypeIDsLikeReauthorization();
        $dischargeIds = PatientDocumentType::getFileTypeIDsLikeDischarge();
        $typeIds = array_merge($rrIds, $dischargeIds);

        $patientDocumentCount = PatientDocument::select([
            'patient_documents.id AS document_id',
        ])
            ->whereIn('document_type_id', $typeIds)
            ->leftJoin('patient_document_shared', function ($join) {
                $join->on('patient_document_shared.patient_documents_id', '=', 'patient_documents.id')
                    ->on('patient_document_shared.document_model', '=', DB::raw("'App\\\\PatientDocument'"));
            })
            ->leftJoin('sent_documents', function ($join) {
                $join->on('sent_documents.document_id', '=', 'patient_documents.id')
                    ->on('sent_documents.document_model', '=', DB::raw("'PatientDocument'"));
            })
            ->whereNull('sent_documents.id')
            ->whereNull('patient_document_shared.id')
            ->count();

        $asessmentRrIds = AssessmentForm::getFileTypeIDsLikeReauthorization();
        $assessmentDischargeIds = AssessmentForm::getFileTypeIDsLikeDischarge();
        $assessmentTypeIds = array_merge($asessmentRrIds, $assessmentDischargeIds);

        $assessmentInitialIds = AssessmentForm::getFileTypeIDsLikeInitialAssessment();
        $assessmentTypeIds = array_merge($assessmentTypeIds, $assessmentInitialIds);

        $assessmentFormCount = PatientAssessmentForm::select([
            'patients_assessment_forms.id AS document_id',
        ])
            ->whereIn('patients_assessment_forms.assessment_form_id', $assessmentTypeIds)
            ->leftJoin('patient_document_shared', function ($join) {
                $join->on('patient_document_shared.patient_documents_id', '=', 'patients_assessment_forms.id')
                    ->on('patient_document_shared.document_model', '=', DB::raw("'App\\\\PatientAssessmentForm'"));
            })
            ->leftJoin('sent_documents', function ($join) {
                $join->on('sent_documents.document_id', '=', 'patients_assessment_forms.id')
                    ->on('sent_documents.document_model', '=', DB::raw("'PatientAssessmentForm'"));
            })
            ->whereNull('sent_documents.id')
            ->whereNull('patient_document_shared.id')
            ->count();
        $patientElectronicDocumentCount = PatientElectronicDocument::query()
            ->select([
                'patient_electronic_documents.id AS document_id',
            ])->whereIn('patient_electronic_documents.document_type_id', $assessmentTypeIds)
            ->leftJoin('patient_document_shared', function ($join) {
                $join->on('patient_document_shared.patient_documents_id', '=', 'patient_electronic_documents.id')
                    ->on('patient_document_shared.document_model', '=', DB::raw("'App\\\\Models\\\\Patient\\\\PatientElectronicDocument'"));
            })->leftJoin('sent_documents', function ($join) {
                $join->on('sent_documents.document_id', '=', 'patient_electronic_documents.id')
                    ->on('sent_documents.document_model', '=', DB::raw("'PatientElectronicDocument'"));
            })
            ->whereNull('sent_documents.id')
            ->whereNull('patient_document_shared.id')
            ->count();

        return $patientDocumentCount + $assessmentFormCount + $patientElectronicDocumentCount;
    }

    public function getPatientDocumentDefaultAddresses($document)
    {
        $addresses = [];
        $id = $document->document_id;
        if ($document->document_model === 'PatientAssessmentForm') {
            $patientDocument = PatientAssessmentForm::where('id', '=', $id)
                ->first();

            $documentType = PatientDocumentType::where('type', '=', $patientDocument->assessmentFormTemplate->title)
                ->first();
        } else if ($document->document_model === 'PatientDocument') {
            $patientDocument = PatientDocument::withoutGlobalScope(DocumentsForAllScope::class)
                ->where('id', '=', $id)
                ->with('documentType')
                ->first();
            $documentType = $patientDocument->documentType;
        } else if ($document->document_model === 'PatientElectronicDocument') {
            $patientDocument = PatientElectronicDocument::find($id);

            $documentType = PatientDocumentType::where('type', '=', $patientDocument->type->title)
                ->first();
        }

        if (isset($patientDocument) && isset($documentType->defaultAddress)) {
            $addresses['email'] = $documentType->defaultAddress->email;
            $addresses['fax'] = $documentType->defaultAddress->fax;
        }

        return $addresses;
    }

    public function markDocumentAsSent(Request $request)
    {
        $this->validate($request, [
            'document_id'    => 'required|numeric',
            'document_model' => 'required|string|max:255',
            'is_sent'        => 'required|boolean',
        ]);

        $sentDoc = SentDocument::updateOrCreate([
            'document_id'    => $request->document_id,
            'document_model' => $request->document_model,
        ], [
            'document_id'    => $request->document_id,
            'document_model' => $request->document_model,
            'user_id'        => \Auth::id(),
            'is_sent'        => $request->is_sent,
        ]);

        $sender = Auth::user()->meta->firstname . ' ' . Auth::user()->meta->lastname;
        $comment = __('comments.document_sent', ['sender' => $sender]);
        if($request->document_model === 'PatientElectronicDocument') {
            $documentModel = PatientElectronicDocument::class;
        } else {
            $documentModel = 'App\\' . $request->document_model;
        }
        PatientDocumentComment::create([
            'admin_id'             => Auth::id(),
            'document_model'       => $documentModel,
            'patient_documents_id' => $request->document_id,
            'is_system_comment'    => true,
            'content'              => $comment,
        ]);

        return response($sentDoc, 201);
    }

    public function approveSentDocument(Request $request)
    {
        $this->validate($request, [
            'document_id'      => 'required|numeric',
            'document_model'   => 'required|string|max:255',
            'is_approved'      => 'required|boolean',
            'authorization_no' => 'nullable|string:max:255',
        ]);

        $sentDoc = SentDocument::updateOrCreate([
            'document_id'    => $request->document_id,
            'document_model' => $request->document_model,
        ], [
            'document_id'      => $request->document_id,
            'document_model'   => $request->document_model,
            'user_id'          => \Auth::id(),
            'is_sent'          => true,
            'approved_at'      => $request->is_approved ? Carbon::now() : null,
            'authorization_no' => ($request->filled('authorization_no') ? $request->authorization_no : null),
        ]);

        return response($sentDoc, 201);
    }

    public function changeStatus(ChangeDocumentStatus $request)
    {
        $updatedCount = PatientDocument::withoutGlobalScope(DocumentsForAllScope::class)
            ->where('id', $request->document_id)
            ->update([
                'only_for_admin' => $request->only_for_admin,
            ]);

        if ($updatedCount > 0) {
            event(new PatientDocumentStatusChanged($request->document_id, $request->only_for_admin));
        }

        return response([
            'success' => true,
        ]);
    }


}
