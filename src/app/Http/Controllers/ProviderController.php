<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Utils\AccessUtils;
use App\Http\Requests\Patient\Search;
use App\Http\Requests\Provider\GetProviderList as GetProviderListRequest;
use App\Http\Requests\SetReadProviderMessage;
use App\Jobs\CalculateFeePerVisit;
use App\Models\Patient\Comment\PatientCommentMention;
use App\Models\Patient\Lead\PatientLeadComment;
use App\Models\TreatmentModality;
use App\OfficeRoom;
use App\Option;
use App\Patient;
use App\PatientDocument;
use App\PatientDocumentComment;
use App\PatientStatus;
use App\Provider;
use App\Role;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use App\Status;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProviderController extends Controller
{
    use PatientTrait, OfficeTrait, StatusTrait, RemoveArrayItems, AccessUtils;

    public function __construct()
    {
        $this->middleware('user-provider', [
            'except' => [
                'isDoctorPasswordValid',
                'getProviderList'
            ]
        ]);
    }

    public function getProvider()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $provider = [];
        } else {
            $provider = Provider::find($user->provider_id);
            $provider->email = $user->email;
        }

        return $provider;
    }

    public function getProviderList(GetProviderListRequest $request)
    {
        return Provider::query()
            ->select('id', 'officeally_id', 'provider_name')
            ->when(! empty($request->with_trashed), function ($query) {
                $query->withTrashed();
            })
            ->orderBy('provider_name')
            ->get();
    }

    /**
     * For search providers patients
     * @param $providerId int
     *
     * @return Patient|Patient[]|Provider|Provider[]|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed|null
     */
    public function getPatients($retrieve = true, $ordered = true)
    {
        if (Auth::user()->isAdmin()) {
            if ($ordered) {
                $patients = Patient::orderBy('first_name');
            } else {
                $patients = Patient::query();
            }
            if ($retrieve) {
                return $patients->get();
            }
            return $patients;
        } else {
            $provider = $this->getProvider();
            if ($retrieve) {
                return $provider->patients;
            }
            return $provider->patients();
        }
    }

    public function setReadMessage(SetReadProviderMessage $request)
    {
        $user = Auth::user();

        $mention = PatientCommentMention::find($request->mention_id);

        $mention->addViewForUser($user->id);

        return response()->json($mention);
    }

    public function getMessages()
    {
        $user = Auth::user();

        $messages = PatientCommentMention::query()
            ->select([
                'id',
                'user_id',
                'comment_id',
                'model',
            ])
            ->with([
                'comment.patient' => function ($query) {
                    $query->select([
                        'id',
                        'first_name',
                        'last_name',
                        'middle_initial',
                        'status_id',
                    ]);
                },
                'comment.patient.status' => function ($query) {
                    $query->select(['id', 'hex_color AS status_color']);
                },
                'comment.provider' => function ($query) {
                    $query->select(['id', 'provider_name']);
                },
            ])
            ->where(function ($query) use (&$user) {
                if ($user->canSeeAllSidebarMessages()) {
                    $query
                        ->where('patient_comment_mentions.created_at', '>', $user->created_at)
                        ->whereDoesntHave('views', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        });
                } else {
                    $query
                        ->where('user_id', $user->id)
                        ->whereDoesntHave('views', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        });
                }
            })
            ->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query->where('model', 'PatientComment')
                            ->whereHas('comment');
                    })
                    ->orWhere(function ($query) {
                        $query->where('model', 'PatientDocumentComment')
                            ->whereHas('documentComment');
                    })
                    ->orWhere(function ($query) {
                        $query->where('model', 'PatientLeadComment')
                            ->whereHas('leadComment');
                    });
            })
            ->latest()
            ->paginate(20);

        $messagesDataset = collect();

        foreach ($messages as &$message) {
            if ($message['model'] === 'PatientDocumentComment') {
                $comment = PatientDocumentComment::query()
                    ->where('id', $message['comment_id'])
                    ->first();

                $commentModel = $comment['document_model'];

                if ($commentModel === PatientDocument::class) {
                    $document = $commentModel::withoutGlobalScope(DocumentsForAllScope::class)
                        ->where('id', $comment['patient_documents_id'])
                        ->first();
                } else {
                    $document = $commentModel::find($comment['patient_documents_id']);
                }

                if ($document === null) {
                    continue;
                }

                $patient = $document->patient()
                    ->select([
                        'patients.id',
                        'first_name',
                        'last_name',
                        'middle_initial',
                        'status_id',
                    ])
                    ->with([
                        'status' => function ($query) {
                            $query->select(['id', 'hex_color AS status_color']);
                        },
                    ])
                    ->first();

                $comment['patient'] = $patient;
                $comment['patient_id'] = $patient->id;
                unset($message['comment']);

                $message['comment'] = $comment;
            } else if ($message['model'] === 'PatientLeadComment') {
                $comment = PatientLeadComment::query()
                    ->where('id', $message['comment_id'])
                    ->first();

                $patientLead = $comment->patientLead;
                $comment['patient_lead'] = $patientLead;
                $comment['patient_lead']['inquiry'] = $patientLead->inquiries->first();
                unset($comment['patient_lead']['inquiries']);
                unset($message['comment']);

                $message['comment'] = $comment;
            }

            if (empty($message->comment->provider)) {
                $message['comment']['provider_name'] = UserMeta::selectRaw("CONCAT(firstname, ' ', lastname) AS provider_name")
                    ->where('user_id', $message->comment->admin_id)
                    ->first()['provider_name'];
            }

            $messagesDataset->push($message);
        }

        return response()->json([
            'meta' => [
                'pagination' => array_except($messages->toArray(), ['data'])
            ],
            'data' => $messagesDataset
        ]);
    }


    public function patientsSearch(Search $request)
    {
        $visit_created_id = Status::getVisitCreatedId();

        $user = Auth::user();

        $pnWhereClause = "";
        $apptWhereClause = "";
        if (!$user->isAdmin()) {
            $pnWhereClause = " AND patient_notes.provider_id={$user->provider_id}";
            $apptWhereClause = " AND appointments.providers_id={$user->provider_id}";
        }

        $providerPatients = $this->getPatients(false, false);
        $statusesCountDB = (clone $providerPatients)->selectRaw('COUNT(*) as patients_count, status')->join(DB::raw('patient_statuses AS ps'), 'ps.id', '=', 'status_id')->groupBy('status_id')->get();
        $statusesCount = $statusesCountDB->pluck('patients_count', 'status');


        $providerPatients = $providerPatients->select([
            'patients.id',
            'patients.patient_id AS office_ally_patient_id',
            'patients.date_of_birth',
            'patient_statuses.hex_color AS status_color',
            'patient_statuses.status AS status_name',
            'completed_appointment_count',
            'first_name',
            'last_name',
            'middle_initial',
            \DB::raw("CONCAT(`first_name`, ' ', `last_name`, ' ', `middle_initial`) AS `full_name`"),
        ]);

        if ($request->filled('q')) {
            $providerPatients = $providerPatients
                ->where(function ($query) use ($request) {
                    $phone =  sanitize_phone($request->q);
                    $query->where('patients.patient_id', '=', $request->q)
                        ->orWhere(DB::raw('CONCAT(`first_name`, \' \', `last_name`, \' \', `middle_initial`)'), 'LIKE', '%' . $request->q . '%')
                        ->when($phone, function ($query, $phone) {
                            $query->orWhere('home_phone', 'like', "%$phone%")
                                ->orWhere('cell_phone', 'like', "%$phone%")
                                ->orWhere('home_phone', 'like', "%$phone%");
                        });
                });
        }
        if ($request->has('statuses') && is_array($request->statuses)) {
            $providerPatients = $providerPatients->whereIn('patient_statuses.status', $request->statuses);
        }
        $providerPatients = $providerPatients
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->orderBy('status_name')
            ->orderBy('full_name')
            ->paginate(40);

        $dataset = [];

        foreach ($providerPatients as $patient) {
            if (!key_exists($patient->status_name, $dataset)) {
                $dataset[$patient->status_name] = [];
            }
            $dataset[$patient->status_name][] = $patient;
        }
        ksort($dataset);

        $pagination = array_except($providerPatients->toArray(), 'data');

        return response()->json([
            'meta' => [
                'pagination' => $pagination,
            ],
            'data' => [
                'patients' => $dataset,
                'patient_count' => $providerPatients->total(),
                'statuses_count' => $statusesCount,
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getAppointments()
    {

        if (Auth::user()->isAdmin()) {
            $appointments = Appointment::all();
        } else {
            $provider = $this->getProvider();
            $appointments = $provider->appointments;
        }


        $allItemsCollection = [];

        $appointments->each(function ($app) use (&$allItemsCollection) {

            $patient = $this->removeId($this->getPatient($app->patients_id)['attributes']);

            $office = $this->removeId($this->getOffice($app->offices_id)['attributes']);

            $status = $this->removeId($this->getStatus($app->appointment_statuses_id)['attributes']);

            $app = $app['attributes'];

            $allItemsCollection[] = array_merge($app, $patient, $office, $status);
        });

        return $allItemsCollection;
    }

    /**
     * @param bool $retrieve
     * Returns all provider patients
     * @return mixed
     */
    public function providerPatients($retrieve = true)
    {
        set_time_limit(300);
        if (Auth::user()->isAdmin()) {
            $providerPatients = Patient::orderBy('first_name');
        } else {
            $provider = $this->getProvider();
            $providerPatients = $provider->patients();
        }
        //        $providerPatients = $providerPatients->with('appointments');
        if ($retrieve) {
            return $providerPatients->get();
        } else {
            return $providerPatients;
        }
    }

    public function providerTodayPatients()
    {
        $user = Auth::user();
        $patients = Patient::select([
            'patients.id',
            'patients.first_name',
            'patients.last_name',
            'patients.middle_initial',
            'patients.email',
            'patients.secondary_email',
            'patients.cell_phone',
            'patients.home_phone',
            'patients.work_phone',
            'appointments.id AS a_id',
            'appointments.start_completing_date',
            'appointments.new_status_id',
            'appointments.time AS appointment_timestamp',
            'appointments.appointment_statuses_id AS appt_status',
            \DB::raw("DATE(FROM_UNIXTIME(`appointments`.`time`)) AS `appointment_date`"),
            \DB::raw("TIME_FORMAT(FROM_UNIXTIME(`appointments`.`time`), '%h:%i %p') AS `appointment_time`"),
            \DB::raw("IF(`patients`.`eff_stop_date` IS NULL OR UNIX_TIMESTAMP(`patients`.`eff_stop_date`) < UNIX_TIMESTAMP(), 1, 0) AS is_overdue"),
            'patient_statuses.hex_color AS status_color',
        ])
            ->join('appointments', 'appointments.patients_id', '=', 'patients.id')
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->where('appointments.time', '>=', Carbon::today()->timestamp)
            ->where('appointments.time', '<=', Carbon::today()->endOfDay()->timestamp)
            ->whereNull('appointments.deleted_at')
            ->orderBy('appointments.time')
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name');

        if (!$user->isAdmin()) {
            $patients->where('appointments.providers_id', $user->provider_id);
        }

        return response([
            'patients' => $patients->get(),
            'statuses' => [
                'cancel' => Status::getNewCancelStatusesId(),
                'complete' => Status::getCompletedVisitCreatedStatusesId(),
                'reschedule' => Status::getRescheduleStatusesId(),
            ],
        ]);
    }

    public function isDoctorPasswordValid(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string|max:255'
        ]);

        $pass = Option::getOptionValue('doctor_password');
        $isPasswordValid = false;
        if (Hash::check($request->input('password'), $pass)) {
            $isPasswordValid = true;
        }

        return response()->json([
            'valid' => $isPasswordValid,
        ], 200);
    }

    public function getMissingNotesCount()
    {
        if (Auth::user()->isAdmin()) {
            return response([
                'missing_note_count' => 0
            ]);
        }
        $archivedStatus = PatientStatus::getArchivedId();
        $statuses = PatientStatus::query()->where('id', '!=', $archivedStatus)->pluck('id')->toArray();
        $patients = Provider::getPatientsWithMissingNotes(Auth::user()->provider_id, $statuses);
        $missingCount = 0;
        foreach ($patients as $patient) {
            $missingCount += $patient['missing_note_count'];
        }
        return response([
            'missing_note_count' => $missingCount,
        ]);
    }

    public function getProvidersDatasetForTribute()
    {
        $auditProviderIds = User::query()
            ->select('users.provider_id')
            ->join('user_roles', 'user_roles.user_id', 'users.id')
            ->join('roles', 'user_roles.role_id', 'roles.id')
            ->where('roles.role', 'insurance_audit')
            ->pluck('provider_id');

        $providers = Provider::query()
            ->select(['users.id AS id', 'providers.provider_name AS key', 'providers.provider_name AS value'])
            ->join('users', function($join) {
                $join->on('users.provider_id', '=', 'providers.id')->whereNull('users.deleted_at');
            })
            ->whereNotIn('providers.id', $auditProviderIds)
            ->orderBy('providers.provider_name')
            ->get()
            ->toArray();

        // array_unshift($providers, ['id' => '-7777', 'key' => 'CWR Admin', 'value' => 'CWR Admin']);

        $adminRole = Role::where('role', '=', 'admin')->first();
        $secretaryRole = Role::where('role', '=', 'secretary')->first();
        $patientRelationManagerRole = Role::where('role', '=', 'patient_relation_manager')->first();

        $users = [];

        if (!is_null($patientRelationManagerRole)) {
            $users = array_merge($users, $patientRelationManagerRole->users()
                ->with(['meta', 'roles'])
                ->get()
                ->toArray());
        }

        if (!is_null($secretaryRole)) {
            $users = array_merge($users, $secretaryRole->users()
                ->with(['meta', 'roles'])
                ->get()
                ->toArray());
        }

        if (!is_null($adminRole)) {
            $users = array_merge($users, $adminRole->users()->with(['meta', 'roles'])->get()->toArray());
        }

        foreach ($users as $user) {
            if (!is_null($user['meta'])) {
                $userName = '';
                if (key_exists('firstname', $user['meta']) && !is_null($user['meta']['firstname'])) {
                    $userName = $user['meta']['firstname'];
                }
                if (key_exists('lastname', $user['meta']) && !is_null($user['meta']['lastname'])) {
                    $userName .= ' ' . $user['meta']['lastname'];
                }
                $keyUserName = $userName;
                if (in_array('admin', array_flatten($user['roles']))) {
                    $keyUserName .= " (Admin)";
                } else if (in_array('patient_relation_manager', array_flatten($user['roles']))) {
                    $keyUserName .= " (Patient Relation Manager)";
                } else {
                    $keyUserName .= " (Secretary)";
                }
                $data = [
                    'id' => $user['id'],
                    'key' => $keyUserName,
                    'value' => $userName,
                ];
                if (!empty($userName) && !in_array($data, $providers)) {
                    array_unshift($providers, $data);
                }
            }
        }

        return response($providers);
    }

    public function hasPatient($id)
    {
        $user = Auth::user();
        if ($user->isInsuranceAudit()) {
            $audit = true;
        } else {
            $audit = false;
        }
        if ($user->isAdmin()) {
            $response = true;
            $supervisorAccess = false;
        } else {
            $response = $this->isUserHasAccessRightsForPatient($id, $user);
            $supervisorAccess = $user->provider->checkSupervisorAccessToPatient($id);
        }

        
        return response(['has' => $response, 'audit' => $audit, 'supervisor' => $supervisorAccess]);
    }

    public function getProviderSignature(Request $request)
    {
        $signatureBase64 = null;
        $currentUser = Auth::user();

        if ($currentUser->isAdmin() || Provider::findOrFail($request->provider_id)->users()->where('id', $currentUser->id)->exists()) {
            $user = Provider::findOrFail($request->provider_id)->user;
            $userMeta = $user->signature;
            $signature = $userMeta->signature;

            if (Storage::disk('signatures')->exists($signature)) {
                $signatureBase64 = ImageHelper::getBase64ImageThumbnail($signature, true, 'signatures');
            }
        }

        return ['signature' => $signatureBase64];
    }

    public function getFeePerVisit(Patient $patient, TreatmentModality $treatmentModality)
    {
        $feePerVisit = \Bus::dispatchNow(new CalculateFeePerVisit($patient, Auth::user()->provider, $treatmentModality));
        return response()->json(['fee_per_visit' => $feePerVisit]);
    }
}
