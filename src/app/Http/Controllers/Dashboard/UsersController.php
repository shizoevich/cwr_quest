<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\Constant\FaxConst;
use App\Helpers\Constant\LoggerConst;
use App\Helpers\Logger\LogActivityFax;
use App\Jobs\GenerateUserSignature;
use App\Jobs\Google\CreateUser;
use App\Patient;
use App\PatientAssessmentForm;
use App\PatientComment;
use App\PatientDocument;
use App\PatientNote;
use App\PatientStatus;
use App\Provider;
use App\Repositories\ReauthorizationRequestDashboard\ReauthorizationRequestDashboardRepositoryInterface;
use App\Role;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use App\Status;
use App\TariffPlan;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Dashboard\Users\Store as StoreUserRequest;
use App\Http\Requests\Dashboard\Users\SendSmsToUpdateSignature as SendSmsToUpdateSignatureRequest;
use App\Http\Requests\Users\ChangeRole;
use App\Http\Requests\Users\GetStatisticRequest;
use App\Http\Requests\Users\UpdateSecretary;
use App\Jobs\Notifications\RingcentralSms;
use App\Models\FaxModel\Fax;
use App\Models\FaxModel\FaxComment;
use App\PatientDocumentType;
use App\Repositories\Fax\FaxRepositoryInterface;
use App\Repositories\Patient\PatientRepositoryInterface;
use Illuminate\Support\Str;
use Twilio\Exceptions\RestException;

class UsersController extends Controller
{
    /**
     * @var PatientRepositoryInterface
     */
    protected $patientRepository;

    /**
     * @var FaxRepositoryInterface
     */
    protected $faxRepository;

    /**
     * @var ReauthorizationRequestDashboardRepositoryInterface
     */
    protected $reauthorizationRequestDashboardRepository;

    public function __construct(
        PatientRepositoryInterface $patientRepository,
        FaxRepositoryInterface $faxRepository,
        ReauthorizationRequestDashboardRepositoryInterface $reauthorizationRequestDashboardRepository
    ) {
        $this->patientRepository = $patientRepository;
        $this->faxRepository = $faxRepository;
        $this->reauthorizationRequestDashboardRepository = $reauthorizationRequestDashboardRepository;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $providers = Provider::query()
            ->whereDoesntHave('users', function($query) {
                $query->whereHas('meta', function($query) {
                    $query->where('has_access_rights_to_reassign_page', false);
                });
            })
            ->orderBy('provider_name')
            ->get(['id', 'provider_name']);
        $tariffPlans = TariffPlan::query()->orderBy('name')->get(['id', 'name']);

        return view('dashboard.users.create', [
            'providers' => $providers,
            'tariffPlans' => $tariffPlans,
        ]);
    }

    /**
     * @param StoreUserRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreUserRequest $request)
    {
        $userRole = !$request->input('user_role') || Auth::user()->isSecretary() ? 'provider' : $request->input('user_role');
        $userPayload = [
            'email' => $request->get('email'),
            'provider_id' => $request->get('provider_id'),
            'password' => bcrypt(str_random(10)),
        ];
        if ($userRole !== 'provider') {
            $userPayload['profile_completed_at'] = Carbon::now();
        }

        $user = User::create($userPayload);
        $roleId = Role::getRoleId($userRole);
        $user->roles()->sync($roleId);

        if ($userRole !== 'provider') {
            $user->meta()->update([
                'firstname' => $request->get('first_name'),
                'lastname' => $request->get('last_name')
            ]);
        }

        dispatch(new GenerateUserSignature($user->id));
        $user->therapistSurvey()->create($request->only([
            'first_name',
            'last_name',
            'personal_email',
        ]));
        if ($request->get('tariff_plan_id') !== null) {
            \DB::table('providers_tariffs_plans')
                ->updateOrInsert([
                    'provider_id' => $user->provider_id,
                ], [
                    'provider_id'    => $user->provider_id,
                    'tariff_plan_id' => $request->get('tariff_plan_id'),
                ]);
        }
        dispatch(new CreateUser($user));

        return redirect()->route('dashboard-doctors');
    }

    /**
     * @param Request $request
     * Delete user by id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        $this->validate($request, [
            'user_id' => 'required|numeric',
        ]);
        $user = User::find($request->input('user_id'));
        $userEmail = $user->email;
        $user->forceDelete();
        return response()->json([
            'success' => true,
            'message' => "User '$userEmail' successfully deleted."
        ], 200);
    }

    /**
     * @param Request $request
     * Upload photo and insert photo name into db
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savePhoto(Request $request) {
        throw new \Exception('This method is not used.');
        $this->validate($request, [
            'photo' => 'required|mimes:gif,jpeg,png',
            'userID' => 'required|numeric|exists:users_meta,user_id'
        ]);
        $photo = $request->file('photo');
        $fileName = md5(uniqid(time())) . '.' . $photo->getClientOriginalExtension();
        $userMeta = UserMeta::where('user_id', $request->input('userID'))->firstOrFail();
        Storage::disk('photos')->put($fileName, file_get_contents($photo));
        if (!empty($userMeta->photo)) {
            Storage::disk('photos')->delete($userMeta->photo);
        }
        $userMeta->photo = $fileName;
        $userMeta->save();

        return response()->json([
            'message' => 'Picture successfully uploaded.'
        ]);
    }

    public function downloadPhoto($id) {
        throw new \Exception('This method is not used.');
        $meta = UserMeta::where('user_id', $id)->firstOrFail();
        $photoName = $meta->photo;
        if (empty($photoName)) {
            return back();
        }
        $disk = Storage::disk('photos');
        if (!$disk->has($photoName)) {
            $meta->photo = '';
            $meta->save();
            return back();
        }
        $stream = $disk
            ->getDriver()
            ->readStream($photoName);
        $mimeType = $disk->mimeType($photoName);
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            "Content-disposition" => "attachment; filename=\"" . $photoName . "\"",
        ]);
    }

    public function sendSmsToUpdateSignature(SendSmsToUpdateSignatureRequest $request) {
        $token = Str::random(20);
        $user = User::find($request->input('user_id'));

        $user->signature_token = $token;
        $user->save();

        $url = url("/signature/{$user->signature_token}");
        $message = __('messages.singature_update', ['url' => $url]);
        $result = \Bus::dispatchNow(new RingcentralSms($request->input('phone'), $message));

        if (!data_get($result, 'status')) {
            throw new RestException(data_get($result, 'message'), 0, 422);
        }

        return response()->json($result);
    }

    public function showUpdateSignatureForm(Request $request) {
        $token = $request->token;
        $user = User::where('signature_token', $token)->first();
        $error = empty($user) ? 'The provided token is invalid or expired.' : null;

        return view('dashboard.doctors.signature', compact(
            'token',
            'user',
            'error'
        ));
    }

    /**
     * @param Request $request
     * Upload signature png photo and insert photo name into db
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSignature(Request $request) {
        $signature = $request->input('signature');
        list($type, $signature) = explode(';', $signature);
        list(, $signature) = explode(',', $signature);
        $fileName = md5(uniqid(time())) . '.png';

        $userMeta = UserMeta::where('user_id', $request->input('userID'))->firstOrFail();

        Storage::disk('signatures')->put($fileName, base64_decode($signature), 'public');
        if (!empty($userMeta->signature)) {
            Storage::disk('signatures')->delete($userMeta->signature);
        }

        $userMeta->signature = $fileName;
        $userMeta->save();

        return response()->json([
            'message' => 'Signature successfully uploaded.',
            'redirectTo' => route('profile.index', ['id' => $request->input('userID')]),
        ]);
    }

    public function saveSignatureWithToken(Request $request) {
        $user = User::where('signature_token', $request->token)->first();
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided token is invalid or expired.',
            ], 400);
        }

        $signature = $request->input('signature');
        list($type, $signature) = explode(';', $signature);
        list(, $signature) = explode(',', $signature);
        $fileName = md5(uniqid(time())) . '.png';

        $userMeta = UserMeta::where('user_id', $user->id)->firstOrFail();

        Storage::disk('signatures')->put($fileName, base64_decode($signature), 'public');
        if (!empty($userMeta->signature)) {
            Storage::disk('signatures')->delete($userMeta->signature);
        }

        $userMeta->signature = $fileName;
        $userMeta->save();

        $user->signature_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Signature successfully uploaded.',
        ]);
    }

    /**
     * Returns list of patients who hasn't appointments to future (with providers and appointments) (Patients without upcoming appointments)
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getStatistic(GetStatisticRequest $request)
    {
        $filters = [];

        $providerId = $request->input('providerId');
        $statuses = $request->input('statuses');

        $user = Auth::user();
        if (!$user->isAdmin()) {
            if (!$user->isProviderAttached()) {
                abort(403);
            }
            $providerId = $user->provider_id;
        }

        if (intval($providerId) !== -1) {
            $filters['provider_id'] = $providerId;
        }

        if (isset($statuses)) {
            $filters['patient_statuses'] = $statuses;
        }

        return $this->patientRepository->getPatientsWithoutUpcomingAppointments($filters);
    }

    public function getPatientAssignedToTherapistsStatisticForDiagrams(Request $request) {

        $statuses = $request->input('statuses');
        $statusesWhereClause = "";
        if(!empty($statuses)) {
            $statusesWhereClause = " AND `patients`.`status_id` IN (";
            foreach($statuses as $status) {
                $statusesWhereClause .= $status . ",";
            }
            $statusesWhereClause = trim($statusesWhereClause, ',');
            $statusesWhereClause .= ")";
        } else {
            $statusesWhereClause = " AND `patients`.`status_id` IS NULL";
        }
        $providersPatientCount = DB::select(DB::raw("
            SELECT `providers`.`provider_name`, (
                SELECT COUNT(`patients_has_providers`.`providers_id`) 
                FROM `patients_has_providers` 
                JOIN `patients` ON `patients`.`id` = `patients_has_providers`.`patients_id`
                WHERE patients_has_providers.chart_read_only = 0 AND `providers_id`=`providers`.`id` AND `patients`.`watching`=1 $statusesWhereClause
            ) AS `patient_count`
            FROM `providers`
            WHERE `providers`.`deleted_at` IS NULL
            ORDER BY `providers`.`provider_name`
        "));

        $providersPatientCountDataset = [];

        foreach($providersPatientCount as $item) {
            $providersPatientCountDataset['providers'][] = $item->provider_name;
            $providersPatientCountDataset['patient_count'][] = $item->patient_count;
            $providersPatientCountDataset['colors'][] = $this->getRandomColor();
        }

        return response([
            'providers_patient_count' => $providersPatientCountDataset,
        ]);
    }

    public function getStatisticForDiagram()
    {
        $providersPatientCount = DB::select(DB::raw("
            SELECT `providers`.`provider_name`, (
                SELECT COUNT(`patients_has_providers`.`providers_id`) 
                FROM `patients_has_providers` 
                JOIN `patients` ON `patients`.`id` = `patients_has_providers`.`patients_id`
                WHERE `providers_id`=`providers`.`id` AND `patients`.`watching`=1 AND patients_has_providers.chart_read_only = 0
            ) AS `patient_count`
            FROM `providers`
        "));

        $providersPatientCountDataset = [];
        foreach ($providersPatientCount as $item) {
            $providersPatientCountDataset['providers'][] = $item->provider_name;
            $providersPatientCountDataset['patient_count'][] = $item->patient_count;
            $providersPatientCountDataset['colors'][] = $this->getRandomColor();
        }

        $providers = Provider::select(['id', 'provider_name'])
            ->orderBy('provider_name')
            ->get();

        $newStatusId = PatientStatus::getNewId();
        $activeStatusId = PatientStatus::getActiveId();

        $patientsCount = 0;
        foreach ($providers as $provider) {
            $providerPatientsWithNoAppointmentsCount['providers'][] = $provider->provider_name;

            $patientsWithoutUpcomingAppointments = $this->patientRepository->getPatientsWithoutUpcomingAppointments(['provider_id' => $provider->id]);
            $patientsWithoutUpcomingAppointmentsCount = $patientsWithoutUpcomingAppointments->count();
            $activePatientsCount = $provider
                ->patients()
                ->whereIn('status_id', [$newStatusId, $activeStatusId])
                ->count();
            $activePatientsWithoutUpcomingAppointments = $patientsWithoutUpcomingAppointments->filter(function ($value) use ($newStatusId, $activeStatusId) {
                return in_array($value->status_id, [$newStatusId, $activeStatusId]);
            });
            $activePatientsWithoutUpcomingAppointmentsCount = $activePatientsWithoutUpcomingAppointments->count();

            $providerPatientsWithNoAppointmentsCount['patient_count'][] = $patientsWithoutUpcomingAppointmentsCount;
            $providerPatientsWithNoAppointmentsCount['active_patient_count'][] = $activePatientsCount;
            $providerPatientsWithNoAppointmentsCount['active_patient_count_with_no_appointments'][] =
                $activePatientsWithoutUpcomingAppointmentsCount;

            $patientsCount += $patientsWithoutUpcomingAppointmentsCount;
        }

        $providerPatientsWithNoAppointmentsCount['colors'] = $providersPatientCountDataset['colors'];

        return response([
            'total_number_of_patients_without_upcoming_appointments' => $patientsCount,
            'providers_patient_count' => $providersPatientCountDataset,
            'provider_patients_with_no_appointments' => $providerPatientsWithNoAppointmentsCount,
        ]);
    }

    private function getRandomColor() {
        $letters = explode(',','0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F');
        $color = '#';
        for ($i = 0; $i < 6; $i++ ) {
            $color .= $letters[array_rand($letters)];
        }
        return $color;
    }

    public function getPatientsWithoutAppointmentsStatistic(Request $request) {
        $this->validate($request, [
            'display_depth' => 'required|numeric'
        ]);

        $statuses = $request->input('statuses');

        $patients = Patient::select([
                'patients.id',
                'patients.first_name',
                'patients.last_name',
                'patients.created_at',
                'patients.created_patient_date',
                'patient_statuses.status',
                'patient_statuses.hex_color',
                'patients.primary_insurance',
                'patients.secondary_insurance',
            ])
            ->selectRaw("(SELECT COUNT(`id`) FROM `appointments` WHERE `patients_id` = `patients`.`id`) AS `appointment_count`, DATEDIFF('".Carbon::now()."', `patients`.`created_patient_date`) AS `days`")
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('appointments', 'appointments.patients_id', '=', 'patients.id')
            ->where('patients.hidden_on_patients_without_appointments_statistics', 0)
            ->groupBy('patients.id')
            ->having('appointment_count', 0);

        if(count($statuses)) {
            $patients = $patients->whereIn('patients.status_id', $statuses);
        }

        if($request->display_depth != -1) {
            $patients = $patients->having('days', '<=', $request->display_depth);
        }
        $patients = $patients->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->with(['providers' => function($query) {
                $query->providerNames();
            }])
            ->get();

        foreach($patients as $patient) {
            $patient->created_patient_date_timestamp = strtotime($patient->created_patient_date);
            $patient->created_patient_date = date('m/d/Y', $patient->created_patient_date_timestamp);
        }

        return $patients;
    }

    public function stopWatching(Request $request) {
        $this->validate($request, [
            'patient_id' => 'required|numeric|exists:patients,id',
            'reason' => 'required|string|max:255',
//            'other_reason' => 'string|max:255',
            'comment' => 'required|string|max:255'
        ]);
        $otherReason = $request->input('other_reason');
        if(isset($otherReason)) {
            $this->validate($request, [
                'other_reason' => 'string|max:255',
            ]);
        }
        $patient = Patient::find($request->input('patient_id'));
        $reason = $request->input('reason');
        if($reason === '-1') {
            $reason = $otherReason;
        }
        $commentText = "<h4 class='text-center'>Stop Watching</h4><b>Reason:</b> " . $reason . '<br><b>Comment:</b> ' . $request->input('comment');
        PatientComment::addSystemComment($request->input('patient_id'), $commentText, true);
        $patient->watching = false;
        $patient->save();
//        return $this->getStatistic();
    }

    public function stopWatchingForPatientsWoAppointments(Request $request) {
        $this->validate($request, [
            'patientId' => 'required|numeric|exists:patients,id'
        ]);
        $patient = Patient::find($request->patientId);
        $patient->hidden_on_patients_without_appointments_statistics = true;
        $patient->save();
    }

    public function deleteDocument(Request $request)
    {
        $detachPdf = $request->input('detach_pdf');
        $patientId = $request->input('patient_id');
        $faxId = $request->input('fax_id');

        if (isset($detachPdf)) {
            $patient = Patient::find($patientId);
            $fax = Fax::find($faxId);
            $result = $this->faxRepository->deleteFaxCommentsAndDocuments($patient, $fax);
            return response()->json([
                'success' => $result,
            ]);
        }

        if (!isset($detachPdf)) {
            $this->validate($request, [
                'id' => 'required|numeric',
                'type' => ['required', 'string', 'max:255', 'regex:/^(doc)|(assessment)|(PatientNote)$/'],
            ]);
            switch ($request->input('type')) {
                case 'doc':
                    $document = PatientDocument::query()->withoutGlobalScope(DocumentsForAllScope::class)->where('id', $request->input('id'))->first();
                    optional($document)->detachFromAppointment();
                    if (PatientDocumentType::find($document->document_type_id)->type === FaxConst::FAX_TYPE) {
                        $faxData = Fax::where("file_name", $document->aws_document_name)->first();
                        $patientId = is_null($faxData) ? null : $faxData->patient_id;

                        LogActivityFax::addToLog(LoggerConst::FAX_DETACHED_FROM_PATIENT, $faxData->id, $patientId);

                        Storage::disk('patients_docs')->delete($document->aws_document_name);

                        FaxComment::where('fax_id', $faxData->id)->delete();
                        Fax::where('id', $faxData->id)->update([
                            'patient_id' => null,
                            'status_id' => null,
                            'comment_id' => null,
                        ]);
                    }
                    $status = (int)optional($document)->delete();
                    break;
                case 'assessment':
                    $status = PatientAssessmentForm::destroy($request->input('id'));
                    break;
                case 'PatientNote':
                    $status = PatientNote::destroy($request->input('id'));
                    break;
            }

            return response()->json([
                'success' => $status,
            ]);
        }
    }

    public function getUpcomingReauthorizationRequests(Request $request) {
        $this->validate($request, [
            'providerId' => 'required|numeric',
        ]);

        $pID = intval($request->input('providerId'));
        $user = Auth::user();
        if(!$user->isAdmin()) {
            if(!$user->isProviderAttached()) {
                abort(403);
            }
            $pID = $user->provider_id;
        }
        unset($user);

        $episodeStartDateSql = "SELECT episode_start_date FROM upcoming_reauthorization_requests urr WHERE urr.patient_id=patients.id AND urr.deleted_at IS NULL ORDER BY episode_start_date DESC LIMIT 1";

        $patients = Patient::select([
            'patients.id',
            DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"),
            'patients.auth_number as insurance_authorization_number',
            'patients.visits_auth as insurance_visits_auth',
            'patients.visits_auth_left as insurance_visits_auth_left',
            'patients.eff_start_date as insurance_eff_start_date',
            'patients.eff_stop_date as insurance_eff_stop_date',
            'patients.primary_insurance',
            'patients.secondary_insurance',
            'patients.insurance_plan_id',
            DB::raw("DATEDIFF(patients.eff_stop_date, '" . Carbon::now() . "') AS date_diff"),
            'patient_statuses.status',
            'patient_statuses.hex_color',
            DB::raw("($episodeStartDateSql) as episode_start_date")
        ])
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patient_insurances_plans', 'patients.insurance_plan_id', '=', 'patient_insurances_plans.id')
            ->with('insurancePlan')
            ->whereNotNull('patients.insurance_plan_id')
            ->where('patient_insurances_plans.is_verification_required', true)
            ->havingRaw('episode_start_date IS NOT NULL');

        $statuses = $request->input('statuses');
        if(!empty($statuses)) {
            $patients = $patients->whereIn('status_id', $statuses);
        } else {
            $patients = $patients->whereNull('status_id');
        }

        $patients = $patients->havingRaw("date_diff > " . config('app.eff_stop_date_depth'))
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc');

        if($pID != -1) {
            $patients = $patients->whereHas('providers', function($query) use ($pID) {
                $query->providerNames();
                $query->where('id', $pID);
            });
        }

        $patients = $patients->with(['providers' => function($query) {
            $query->providerNames();
        }])->get();

        $this->reauthorizationRequestDashboardRepository->loadReauthorizationRequestDocument($patients);

        return $patients;
    }

    public function getPatientsAssignedToTherapistsStatistic(Request $request) {
        $this->validate($request, [
            'pid' => 'required|numeric'
        ]);

        $providerID = null;

        if(intval($request->input('pid')) !== -1) {
            $providerID = $request->input('pid');
        }
        $user = Auth::user();
        if(!$user->isAdmin()) {
            if(!$user->isProviderAttached()) {
                abort(403);
            }
            $providerID = $user->provider_id;
        }
        unset($user);

        $dataset = [];

        $patientStatuses = PatientStatus::select(['id AS status_id', 'status', 'hex_color'])->orderBy('status')->get();
        $providers = Provider::select(['id', 'provider_name'])->orderBy('provider_name');
        if(!empty($providerID)) {
            $providers->where('id', $providerID);
        }

        $providers = $providers->get();

        $statuses = $request->input('statuses');

        $statusesWhereClause = "";
        if(!empty($statuses)) {
            $statusesWhereClause = " AND `patients`.`status_id` IN (";
            foreach($statuses as $status) {
                $statusesWhereClause .= $status . ",";
            }
            $statusesWhereClause = trim($statusesWhereClause, ',');
            $statusesWhereClause .= ")";
        } else {
            $statusesWhereClause = " AND `status_id` IS NULL";
        }

        $visitCreatedId = Status::getVisitCreatedId();
        foreach($providers as $provider) {
            $arr = [];
            $arr['provider_name'] = $provider->provider_name;
            $arr['statuses'] = $patientStatuses->toArray();
            $res = DB::select(DB::raw("
                SELECT `patients`.`status_id`, COUNT(*) AS `patient_count`
                FROM `patients`
                JOIN `patients_has_providers` ON `patients_has_providers`.`patients_id` = `patients`.`id`
                WHERE `patients_has_providers`.`providers_id` = {$provider->id} AND patients_has_providers.chart_read_only = 0
                GROUP BY `patients`.`status_id`
            "));

            $total = 0;
            for($i = 0; $i < count($arr['statuses']); $i++) {
                if(!empty($res)) {
                    foreach($res as $r) {
                        if($arr['statuses'][$i]['status_id'] === $r->status_id) {

                            $arr['statuses'][$i]['patient_count'] = $r->patient_count;
                            $total += $arr['statuses'][$i]['patient_count'];
                            break;
                        }
                        $arr['statuses'][$i]['patient_count'] = 0;
                    }
                } else {
                    $arr['statuses'][$i]['patient_count'] = 0;
                }
            }
            $arr['statuses'][] = [
                'hex_color' => '000000',
                'patient_count' => $total,
                'status' => 'Total',
            ];
//            $arr['patients'] = $provider->patients()->select(['id', 'first_name', 'last_name'])->with('status')->get();
            $arr['patients'] = DB::select(DB::raw("
                SELECT patients.id, patients.primary_insurance, patients.secondary_insurance, CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name, patient_statuses.status, 
                  patient_statuses.hex_color, DATE_FORMAT(patients_has_providers.created_at, '%m/%d/%Y') AS date_of_assignment,
                  (SELECT COUNT(id) FROM appointments WHERE patients_id = patients.id AND appointment_statuses_id = $visitCreatedId AND deleted_at IS NULL) AS number_of_visit_created
                FROM patients
                JOIN patient_statuses ON patient_statuses.id = patients.status_id
                JOIN patients_has_providers ON patients_has_providers.patients_id=patients.id
                WHERE patients_has_providers.providers_id={$provider->id} AND patients_has_providers.chart_read_only = 0 $statusesWhereClause
                ORDER BY patients.first_name ASC, patients.last_name ASC
            "));
            $dataset[] = $arr;
        }
        if(empty($providerID)) {
            $totalStat['statuses'] = $patientStatuses->toArray();
            $totalStat['patients'] = [];
            $totalStat['provider_name'] = 'All Patients';
            for($i = 0; $i < count($totalStat['statuses']); $i++) {
                $totalStat['statuses'][$i]['patient_count'] = Patient::where('status_id', $totalStat['statuses'][$i]['status_id'])->count();
            }
            array_unshift($dataset, $totalStat);
        }

        return $dataset;
    }

    public function changeRole(ChangeRole $request)
    {
        $user = User::findOrFail($request->get('user'));
        if ($user->isOnlyAdmin()) {
            return response(__('users.role_not_changed '), 403);
        }
        
        $role = $request->get('role');
        $roleId = Role::getRoleId($role);
        $user->roles()->sync($roleId);
        $userData = [
            'id' => $user->id,
            'role' => $request->get('role'),
        ];
        if ($user->provider) {
            $userData['userName'] = $user->provider->provider_name;
        } else {
            $userData['userName'] = "{$user->meta->firstname} {$user->meta->lastname}";
            if (!$user->meta->firstname && !$user->meta->lastname) {
                $userData['modal'] = true;
            }
        }
        $response = [
            'response' => trans('users.role_changed', ['email' => $user->email, 'role' => ucwords(str_replace('_', ' ', $role))]),
            'user' => $userData
        ];
        return response($response, 200);
    }

    public function updateSecretaryMeta(UpdateSecretary $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->meta()->update($request->only(['lastname', 'firstname']));
        return $user->meta;
    }

    public function getSecretaryMeta($userId)
    {
        $user = User::findOrFail($userId);
        return $user->meta;
    }
}
