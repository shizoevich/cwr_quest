<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTridiuumCredentials;
use App\Http\Requests\StoreUserProfile;
use App\Http\Requests\Dashboard\Doctors\SaveDoctorProviderRelation as SaveDoctorProviderRelationRequest;
use App\Http\Requests\Dashboard\Doctors\SaveProviderTariffPlanRelation as SaveProviderTariffPlanRelationRequest;
use App\Http\Requests\Dashboard\Doctors\SaveProviderBillingPeriodRelation as SaveProviderBillingPeriodRelationRequest;
use App\Http\Requests\Dashboard\Doctors\SaveProviderWorkHoursPerWeek as SaveProviderWorkHoursPerWeekRequest;
use App\Http\Requests\Dashboard\Doctors\SaveProviderLicenseDate as SaveProviderLicenseDateRequest;
use App\Http\Requests\Dashboard\Doctors\SaveProviderLicenseEndDate as SaveProviderLicenseEndDateRequest;
use App\Http\Requests\Dashboard\Doctors\SaveProviderHasBenefits as SaveProviderHasBenefitsRequest;
use App\Http\Requests\Dashboard\Doctors\SaveProviderCollectPaymentAvailable;
use App\Http\Requests\Dashboard\Doctors\SaveProviderIsAssociate;
use App\Http\Requests\Dashboard\Doctors\SaveProviderIsNew;
use App\Http\Requests\Dashboard\Doctors\SaveProviderWorksWithUpheal;
use App\Jobs\GenerateUserSignature;
use App\Jobs\Mail\Invite as InviteMail;
use App\Jobs\Salary\UpdateSalaryDataWhenProviderTariffPlanChanged;
use App\Jobs\TherapistSurvey\TherapistSurveyStore;
use App\Jobs\TridiuumCredentialStore;
use App\Models\Billing\BillingPeriodType;
use App\Models\Patient\PatientElectronicDocument;
use App\Models\Provider\ProviderSupervisor;
use App\Models\Therapist\TherapistSurveyPatientCategory;
use App\Models\Therapist\TherapistSurveyEthnicity;
use App\Models\Therapist\TherapistSurveyLanguage;
use App\Models\Therapist\TherapistSurveyRace;
use App\Models\Therapist\TherapistSurveySpecialty;
use App\Models\Therapist\TherapistSurveyTreatmentType;
use App\Models\TridiuumProvider;
use App\Patient;
use App\PatientAssessmentForm;
use App\PatientDocumentType;
use App\PatientInsurance;
use App\PatientNote;
use App\PatientStatus;
use App\Provider;
use App\Status;
use App\TariffPlan;
use App\TherapistSurveyAgeGroup;
use App\TherapistSurveyTypeOfClient;
use App\User;
use App\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\Providers\ProviderSummaryTrait;
use App\Jobs\Availability\GetDoctorsAvailability;
use App\Repositories\Provider\Comments\ProviderCommentRepositoryInterface;
use App\Helpers\UphealHelper;

class DoctorsController extends Controller
{
    use ProviderSummaryTrait;

    /**
     * @var ProviderCommentRepositoryInterface
     */
    protected $providerCommentRepository;

    /**
     * ProviderCommentController constructor.
     * @param ProviderCommentRepositoryInterface $providerCommentRepository
     */
    public function __construct(ProviderCommentRepositoryInterface $providerCommentRepository)
    {
        $this->providerCommentRepository = $providerCommentRepository;
    }

    /**
     * Show doctors page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('dashboard.doctors.index');
    }

    public function indexApi() {
        $roles[] = Role::getRoleId('admin');
        $users = User::withTrashed()->with([
            'meta' => function ($query) {
                $query->withTrashed();
            }, 
            'provider' => function ($query) {
                $query->withTrashed();
            }
        ])
            ->select('users.*', 'providers.provider_name as provider_name', DB::raw("CONCAT(users_meta.firstname, ' ',  users_meta.lastname) as secretary_name"))
            ->leftJoin('users_meta', 'users_meta.user_id', '=', 'users.id')
            ->leftJoin('providers', 'users.provider_id', '=', 'providers.id')
            ->whereDoesntHave('roles', function($query) use (&$roles) {
                $query->where('role_id', $roles);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $providers = $users->reduce(function ($carry, $item) {
            if (isset($item->provider)) {
                $carry->push($item->provider);
            }
            
            return $carry;
        }, collect([]));

        $totalWorkedYearsMapping = $this->getTotalWorkedYearsMapping($providers);
        
        $usersObject = [];
        foreach ($users as $k => $user) {
            $usersObject[$k] = $user;
            $usersObject[$k]['index'] = $k + 1;
            if ($user->isSecretary()) {
                $usersObject[$k]['name'] = $user->secretary_name ? $user->secretary_name : '';
            } else {
                $usersObject[$k]['name'] = $user->isProviderAttached() ? $user->provider_name : ''; 
            }

            $tridiuum = '-';
            if (!$user->isAdmin() && !$user->trashed()) {
                if (optional($user->provider)->tridiuumProvider) {
                    $tridiuum = '<span class="text-success">active</span>';
                } else {
                    if ($user->provider) {
                        if (!$user->isInsuranceAudit()) {
                            $tridiuum = '<a class="btn btn-primary" href="/profile/' . $user->id . '?tab=tab_tridiuum" data-user-id="' . $user->getKey() . '">Assign Account</a>';
                        }
                    } else {
                        $tridiuum = '<span class="text-danger">need provider</span>';
                    }
                }
            }
            $usersObject[$k]['tridiuum'] = $tridiuum;

            $statusClass = $user->trashed() ? 'text-danger' : 'text-success';
            $status = $user->trashed() ? 'not active' : 'active';
            $usersObject[$k]['status'] = '<span class="status ' . $statusClass . '">' . $status . '</span>';

            $supervision = '-';
            if (!$user->isAdmin()) {
                if (optional($user->provider)->is_supervisor) {
                    $supervision = '<span class="text-success">active</span>';
                } else {
                    $supervision = '<span class="text-danger">not active</span>';
                }
            }
            $usersObject[$k]['supervision'] = $supervision;

            $userActions['class'] = $user->isSecretary() ? 'class="edit-secretary-action"' : '';
            $userActions['href'] = route('profile.index', ['id' => $user->id]);
            $editBtn = '';
            if (!$user->isInsuranceAudit()) {
                $editBtn = '<span class="glyphicon glyphicon-pencil"></span>';
            }
            $usersObject[$k]['actions'] = '<a '. $userActions['class'] .'
                id="user-edit-'. $user->id .'"
                href="'. $userActions['href'] .'" 
                data-user="'. $user->id .'"
                >
                '.$editBtn.'
            </a>';

            if ($user->isProviderAttached() || $user->isAdmin()) {
                $mode = $user->trashed() ? 'enable':'disable';
                $classAction = $user->trashed() ? 'refresh':'remove';
                $usersObject[$k]['actions'] .= '<a class="enable-disable" data-mode="'. $mode .'" data-user="' . $user->id .'" href="javascript:void(0);">
                    <span class="glyphicon glyphicon-'. $classAction .'"></span>
                </a>';
            } else {
                $usersObject[$k]['actions'] .= '<a class="show-confirm-deletion-user-modal" data-user="' .$user->id .'" href="javascript:void(0);">
                    <span class="glyphicon glyphicon-remove"></span>
                </a>';
            }

            $usersObject[$k]['emailFormatted'] = '<a href="mailto:' . $user->email .'" class="user-email">' . $user->email .'</a>';
            $isUserChanges = ($user->trashed() || $user->isInsuranceAudit()) ? 'disabled' : '';
            if (auth()->user()->isOnlyAdmin()) {
                $isSecretary = $user->isSecretary() ? 'selected' : '';
                $isInsuranceAudit = $user->isInsuranceAudit() ? 'selected' : '';
                $isPatientRelationManager = $user->isPatientRelationManager() ? 'selected' : '';
                $usersObject[$k]['role'] = '<select name="secretary" data-placement="top" data-trigger="focus" '. $isUserChanges .' id="select-role-' . $user->id . '" data-user="' .$user->id .'" class="form-control select-role">
                        <option value="provider">Provider</option>
                        <option value="secretary" ' . $isSecretary . '>Secretary</option>
                        <option value="patient_relation_manager" ' . $isPatientRelationManager . '>Patient Relation Manager</option>
                        <option value="insurance_audit" ' . $isInsuranceAudit . ' disabled>Insurance Audit</option>
                    </select>';
            } else {
                $usersObject[$k]['role'] = '';
            }
            
            $usersObject[$k]['rowClass'] = $user->isProviderAttached() || $user->isSecretary() || $user->isPatientRelationManager() ? '' : 'danger';

            $totalWorkedYears = '-';
            if (isset($user->provider_id) && isset($totalWorkedYearsMapping) && isset($totalWorkedYearsMapping[$user->provider_id])) {
                $totalWorkedYears = $totalWorkedYearsMapping[$user->provider_id]['totalWorkedYears'];
            }
            $usersObject[$k]['totalWorkedYears'] = $totalWorkedYears;
            $usersObject[$k]['isNew'] = optional($user->provider)->is_new;
        }
        
        return response()->json($usersObject);
    }

    public function enableOrDisable(Request $request)
    {
        $this->validate($request, [
            'userId' => 'required|numeric',
        ]);
        $user = User::withTrashed()->where('id', $request->input('userId'))
            ->firstOrFail();
        $message = "User {$user->email} successfully ";
        $enabled = false;
        if ($user->trashed()) {
            $user->restore();
            $user->meta()->withTrashed()->first()->restore();
            optional($user->provider()->withTrashed()->first())->restore();
            $message .= "enabled.";
            $enabled = true;
        } else {
            $user->delete();
            $message .= "disabled.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'enabled' => $enabled,
        ], 200);
    }

    /**
     * Show doctors availability page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function availability(Request $request)
    {
        $startDate = Carbon::parse($request->get('start'));
        $endDate = Carbon::parse($request->get('end'));

        $data =  \Bus::dispatchNow(new GetDoctorsAvailability($startDate, $endDate, $request->all(), $request->ajax()));

        if ($request->ajax()) {
            return response()->json($data);
        } else {
            return view('dashboard.doctors.availability', $data);
        }
    }

    /**
     * @param Request $request
     * Save User -> Provider relationship
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDoctorProviderRelation(SaveDoctorProviderRelationRequest $request)
    {
        $userID = $request->input('userId');
        $providerID = $request->input('providerId');
        $model = User::withTrashed()->where('provider_id', $providerID)
            ->first();
        if ($model !== null) {
            return response()->json([
                'success' => false,
                'errorMessage' => 'This doctor has already been assigned.',
            ]);
        }
        $user = User::where('id', $userID)->first();
        $user->provider_id = $providerID;
        if(!$user->hasRole('provider')) {
            $providerRoleId = Role::getRoleId('provider');
            $user->roles()->attach($providerRoleId);
        }
        $user->save();
        $userSignature = $user->meta->signature;
        $disk = Storage::disk('signatures');
        if (!empty($userSignature) && $disk->exists($userSignature)) {
            $disk->delete($userSignature);
        }
        $this->dispatch(new GenerateUserSignature($user->id));

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderTariffPlanRelation(SaveProviderTariffPlanRelationRequest $request)
    {
        if(Auth::user()->isSecretary()) {
            return response()->json([
                'success' => false
            ], 403);
        }

        $providerID = $request->input('providerId');
        $tariffPlanID = $request->input('tariffPlanId');

        $relation = DB::table('providers_tariffs_plans')
            ->where('provider_id', $providerID)
            ->first();
        if($relation) {
            DB::table('providers_tariffs_plans')
                ->where('provider_id', $providerID)
                ->update([
                    'tariff_plan_id' => $tariffPlanID
                ]);
        } else {
            DB::table('providers_tariffs_plans')
                ->insert([
                    'tariff_plan_id' => $tariffPlanID,
                    'provider_id' => $providerID
                ]);
        }
        $dateFrom = Carbon::today();
        if($request->input('date_from')) {
            $dateFrom = Carbon::parse($request->input('date_from'));
        }
        dispatch(new UpdateSalaryDataWhenProviderTariffPlanChanged($tariffPlanID, $providerID, $dateFrom));

        return response()->json([
            'success' => true
        ], 201);
    }
    
    /**
     * Copied from saveProviderTariffPlanRelation()
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveProviderBillingPeriodRelation(SaveProviderBillingPeriodRelationRequest $request)
    {
        if(Auth::user()->isSecretary()) {
            return response()->json([
                'success' => false
            ], 403);
        }

        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $billingPeriodTypeId = $request->input('billingPeriodTypeId');
        $provider->update(['billing_period_type_id' => $billingPeriodTypeId]);
        \Cache::forget("provider:{$provider->getKey()}:billing-period-name");

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderWorkHoursPerWeek(SaveProviderWorkHoursPerWeekRequest $request)
    {
        if(Auth::user()->isSecretary()) {
            return response()->json([
                'success' => false
            ], 403);
        }
        
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $workHoursPerWeek = $request->input('workHoursPerWeek');
        $provider->update(['work_hours_per_week' => $workHoursPerWeek]);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderLicenseDate(SaveProviderLicenseDateRequest $request)
    {
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $licenseDate = $request->input('licenseDate');
        $provider->update(['license_date' => $licenseDate]);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderLicenseEndDate(SaveProviderLicenseEndDateRequest $request)
    {
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $licenseEndDate = $request->input('licenseEndDate');
        $provider->update(['license_end_date' => $licenseEndDate]);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderHasBenefits(SaveProviderHasBenefitsRequest $request)
    {
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $hasBenefits = $request->input('hasBenefits');
        $provider->update(['has_benefits' => $hasBenefits]);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderIsNew(SaveProviderIsNew $request)
    {
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $isNew = $request->input('isNew');
        $provider->update(['is_new' => $isNew]);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderCollectPaymentAvailable(SaveProviderCollectPaymentAvailable $request)
    {
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $isCollectPaymentAvailable = $request->input('isCollectPaymentAvailable');
        $provider->update(['is_collect_payment_available' => $isCollectPaymentAvailable]);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderIsAssociate(SaveProviderIsAssociate $request)
    {
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();
        $isAssociate = $request->input('isAssociate');
        $provider->update(['is_associate' => $isAssociate]);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function saveProviderWorksWithUpheal(SaveProviderWorksWithUpheal $request)
    {
        $worksWithUpheal = $request->input('worksWithUpheal');
        $providerId = $request->input('providerId');
        $provider = Provider::withTrashed()->where('id', $providerId)->first();

        if ($worksWithUpheal && !$provider->upheal_user_id) {
            UphealHelper::createProvider($provider);
        }

        $provider->update(['works_with_upheal' => $worksWithUpheal]);

        return response()->json([
            'success' => true
        ], 201);
    }

    /**
     * @param $id
     * Show signature page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showSignatureForm($id = null)
    {
        if(is_null($id)) {
            $id = \Auth::user()->id;
        }

        $user = User::where('id', $id)->firstOrFail();
        if (!$user->isProviderAttached()) {
            abort(404);
        }

        return view('dashboard.doctors.signature', compact(
            'user'
        ));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function invite(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $this->dispatchNow(new InviteMail($request->get('email')));

        return response()->json([
            'status' => (int) 1,
            'message' => 'Invite successfully sent'
        ]);
    }

    /**
     * @param Request $request
     * Returns statistics for Missing Progress Notes page
     *
     * @return array
     */
    public function getStatistic(Request $request)
    {
        $this->validate($request, [
            'providerId' => 'required|numeric',
        ]);

        $pID = intval($request->input('providerId'));
        $user = Auth::user();
        if (!$user->isAdmin()) {
            if (!$user->isProviderAttached()) {
                abort(403);
            }
            $pID = $user->provider_id;
        }
        unset($user);
        $response = [];
        $providers = Provider::select('provider_name', 'id');

        if ($pID !== -1) {
            $providers = $providers->where('id', $pID);
        }

        $providers = $providers->orderBy('provider_name')->get();

        $statuses = $request->input('statuses');

        $activeStatusID = PatientStatus::getActiveId();

        $providers->each(function (Provider $provider) use (
            &$response,
            $statuses,
            $activeStatusID
        ) {
            $providerImportantPatients = Provider::getPatientsWithMissingNotes($provider->id, $statuses);

            foreach ($providerImportantPatients as $key => &$patient) {
                $missingNoteDates = explode(',', $patient['missing_note_dates']);
                foreach ($missingNoteDates as &$date) {
                    $date = Carbon::createFromTimestamp($date)->format('m/d/Y h:i A');
                }
                $patient['missing_note_dates'] = $missingNoteDates;
            }

            $tmp = array_merge($provider->toArray(),
                ['patients' => $providerImportantPatients]);
            $tmp['total_no_of_active_patients'] = $provider->patients()
                ->where('status_id', $activeStatusID)->count();
            $response[] = $tmp;
        });

        return $response;
    }


    public function getTotalVcAndPnStatistic(Request $request)
    {
        $this->validate($request, [
            'providerId' => 'required|numeric',
            'monthFrom' => 'required',
            'monthTo' => 'required',
        ]);

        $pID = intval($request->input('providerId'));
        $user = Auth::user();
        if (!$user->isAdmin()) {
            if (!$user->isProviderAttached()) {
                abort(403);
            }
            $pID = $user->provider_id;
        }
        unset($user);
        $response = [];
        $visit_created_id = Status::getVisitCreatedId();
        $providers = Provider::select('provider_name', 'id');

        if ($pID !== -1) {
            $providers = $providers->where('id', $pID);
        }

        $providers = $providers->orderBy('provider_name')->get();

        $dateFilter = '';
        $year = Carbon::now()->year;
        $monthFrom = $request->input('monthFrom');
        $monthTo = $request->input('monthTo');

        $monthFrom = (new Carbon("first day of $monthFrom $year"))->timestamp;
        $monthTo = (new Carbon("last day of $monthTo $year"))->addSeconds(24 * 60 * 60 - 1)->timestamp;
//        dump($monthTo);

        $providers->each(function ($provider) use (
            &$response,
            $visit_created_id,
            $monthTo,
            $monthFrom
        ) {
//
//            $providerImportantPatients = DB::select(DB::raw("
//                SELECT `patients`.`id`, CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS full_name, `patients`.`completed_appointment_count`,
//                    (SELECT COUNT(*) FROM `patient_notes` WHERE `patients_id`=`patients`.`id` AND `is_finalized`=1 AND `deleted_at` IS NULL AND UNIX_TIMESTAMP(date_of_service) >= $monthFrom AND UNIX_TIMESTAMP(date_of_service) <= $monthTo) AS `note_count`,
//                    (SELECT COUNT(*) FROM `appointments` WHERE `patients_id`=`patients`.`id` AND appointments.providers_id = {$provider->id} AND `appointments`.`deleted_at` IS NULL AND `appointment_statuses_id`=$visit_created_id AND appointments.time >= $monthFrom AND appointments.time <= $monthTo) AS `appointments_count`
//                FROM `patients`
//                JOIN `appointments` ON `appointments`.`patients_id` = `patients`.`id`
//                WHERE `appointments`.`deleted_at` IS NULL AND `appointments`.`providers_id` = {$provider->id}
//                GROUP BY `patients`.`id`
//                ORDER BY `full_name`
//            "));

            $providerImportantPatients = Patient::select(['patients.id', 'patients.completed_appointment_count', 'appointments.idAppointments'])
                ->selectRaw("
                    CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS full_name,
                    (SELECT COUNT(*) FROM `patient_notes` WHERE `patient_notes`.`provider_name` = '{$provider->provider_name}' AND `patients_id`=`patients`.`id` AND `is_finalized`=1 AND `deleted_at` IS NULL AND UNIX_TIMESTAMP(date_of_service) >= $monthFrom AND UNIX_TIMESTAMP(date_of_service) <= $monthTo) AS `note_count`,
                    (SELECT COUNT(*) FROM `appointments` WHERE `patients_id`=`patients`.`id` AND appointments.providers_id = {$provider->id} AND `appointments`.`deleted_at` IS NULL AND `appointment_statuses_id`=$visit_created_id AND appointments.time >= $monthFrom AND appointments.time <= $monthTo) AS `appointments_count`, COUNT(appointments.id) AS `appointments_count1`
                ")
                ->join('appointments', 'appointments.patients_id', '=', 'patients.id')
                ->where('appointments.providers_id', $provider->id)
//                ->where('appointment_statuses_id', $visit_created_id)
//                ->where('appointments.time', '>=', $monthFrom)
//                ->where('appointments.time', '<=', $monthTo)
                ->groupBy(['patients.id'])
                ->orderBy('full_name')
                ->get()
                ->toArray();

            $tmp = array_merge($provider->toArray(),
                ['patients' => $providerImportantPatients]);
            $response[] = $tmp;
        });

        return $response;
    }

    public function allowEditingNote(Request $request)
    {
        $this->validate($request, [
            'noteId' => 'required|numeric|exists:patient_notes,id'
        ]);

        $note = PatientNote::find($request->input('noteId'));
        $note->start_editing_note_date = Carbon::now();
        $note->save();
    }

    public function allowEditingAssessmentForm(Request $request)
    {
        $this->validate($request, [
            'formId' => 'required|numeric|exists:patients_assessment_forms,id'
        ]);

        $form = PatientAssessmentForm::find($request->input('formId'));
        $form->start_editing_date = Carbon::now();
        $form->save();
    }

    public function deleteNote(Request $request)
    {
        $this->validate($request, [
            'noteId' => 'required|numeric|exists:patient_notes,id'
        ]);

        PatientNote::find($request->input('noteId'))->delete();
    }

    /**
     * @param null $userId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function profileShow($userId = null)
    {
        if(is_null($userId)) {
            if(Auth::user()->isAdmin()) {
                abort(404);
            }
            $userId = Auth::user()->id;
        } else if(Auth::user()->id != $userId) {
            if(!Auth::user()->isAdmin()) {
                abort(403);
            }
        }

        $providers = Provider::withTrashed()->orderBy('provider_name')->get();
        $ageGroups = TherapistSurveyAgeGroup::all();
        $typesOfClients = TherapistSurveyTypeOfClient::all();
        $patientCategories = TherapistSurveyPatientCategory::all();
        $ethnicities = TherapistSurveyEthnicity::all();
        $languages = TherapistSurveyLanguage::all();
        $races = TherapistSurveyRace::all();
        $specialties = TherapistSurveySpecialty::all();
        $treatmentTypes = TherapistSurveyTreatmentType::all();
        $insurances = PatientInsurance::query()
            ->select(['id', 'insurance'])
            ->orderBy('insurance')
            ->get();

        $user = User::query()->where('id', '=', $userId)->with([
            'therapistSurvey.ageGroups',
            'therapistSurvey.typesOfClients',
            'therapistSurvey.patientCategories',
            'therapistSurvey.ethnicities',
            'therapistSurvey.languagesTridiuum',
            'therapistSurvey.races',
            'therapistSurvey.specialties',
            'therapistSurvey.treatmentTypes',
        ])
        ->withTrashed()
        ->firstOrFail();

        if($user->isSecretary()) {
            return back();
        }

        $loginAt = null;
        if(!empty($user->login_at)) {
            $loginAt = Carbon::createFromTimestamp($user->login_at)->format('m/d/Y h:i A');
        }

        $provider = $user->provider;

        if(!empty($provider)) {
            $patientCount = $provider->patients()->count();
            $pnCount = $provider->progressNotes()->where('is_finalized', true)->count();
            $formNewPatientId = PatientDocumentType::getNewPatientId();
            $patientFormsCount = $provider->patients()
                ->join('patient_documents', 'patient_documents.patient_id', '=', 'patients.id')
                ->where('patients.id', '!=', 1111)
                ->where('patient_documents.document_type_id', $formNewPatientId)
                ->count('patients.id');
            $tridiuumProvider = $provider->tridiuumProvider;
            $salary = null;
            $missingNotes = null;
            $supervisors = Provider::where('is_supervisor', 1)->get();
            $currentSupervisor = ProviderSupervisor::getSupervisorForDate($provider->id, Carbon::now());
            $comments = $this->providerCommentRepository->getComments($provider);
        } else {
            $patientCount = null;
            $pnCount = null;
            $patientFormsCount = null;
            $tridiuumProvider = null;
            $salary = null;
            $missingNotes = [];
            $supervisors = [];
            $currentSupervisor = null;
            $comments = [];
        }

        $tariffPlans = TariffPlan::all();
        $billingPeriodTypes = BillingPeriodType::all();
        $tridiuumProviders = TridiuumProvider::query()
            ->select([
                'id',
                \DB::raw("CONCAT(`first_name`, ' ', `last_name`) AS provider_name")
            ])
            ->whereNull('internal_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        
        $totalWorkedYearsMapping = $this->getTotalWorkedYearsMapping($providers);

        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = $startOfYear->copy()->endOfYear();
        $appointmentsCountMapping = $this->getAppointmentsPerYearCountMapping($startOfYear, $endOfYear);

        $tridiuumUrlGuide = asset('videos/tridiuum_url_guide.mp4');

        return view('profile.index', [
            'providers' => $providers,
            'age_groups' => $ageGroups,
            'types_of_clients' => $typesOfClients,
            'patient_categories' => $patientCategories,
            'ethnicities' => $ethnicities,
            'languages' => $languages,
            'races' => $races,
            'specialties' => $specialties,
            'treatment_types' => $treatmentTypes,
            'insurances' => $insurances,
            'user' => $user,
            'edit' => true,
            'patientCount' => $patientCount,
            'pnCount' => $pnCount,
            'loginAt' => $loginAt,
            'patientFormsCount' => $patientFormsCount,
            'tariffPlans' => $tariffPlans,
            'billingPeriodTypes' => $billingPeriodTypes,
            'salary' => $salary,
            'missingNotes' => $missingNotes,
            'redirect' => request('redirect'),
            'tridiuumProvider' => $tridiuumProvider,
            'tridiuumProviders' => $tridiuumProviders,
            'totalWorkedYearsMapping' => $totalWorkedYearsMapping,
            'appointmentsCountMapping' => $appointmentsCountMapping,
            'tridiuum_url_guide' => $tridiuumUrlGuide,
            'supervisors' => $supervisors,
            'currentSupervisor' => $currentSupervisor,
            'comments' => $comments,
        ]);
    }

    public function profileSuperviseesApi($userId)
    {
        $response = [];
        $user = User::find($userId);
        $provider = optional($user)->provider;

        if (isset($provider) && $provider->is_supervisor) {
            $supervisees = ProviderSupervisor::getSuperviseeForToday($provider->id);
            foreach($supervisees as $index => $supervisee) {
                $response[] = [
                    'index' => $index + 1,
                    'provider_name' => $supervisee->provider_name,
                    'is_active' => (bool) $supervisee->is_active,
                    'attached_at' => Carbon::parse($supervisee->attached_at)->format('m/d/Y'),
                    'patients_count' => $supervisee->provider->patients()->count()
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * @param StoreUserProfile $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profileStore(StoreUserProfile $request)
    {
        $userId = $request->get('user_id');

        $result = \Bus::dispatchNow(new TherapistSurveyStore($_POST, $request->file('photo')));

        User::query()->where('id', $userId)->update(['profile_completed_at' => Carbon::now()]);

        if($request->has('redirect') && !empty($request->get('redirect'))) {
            return redirect($request->get('redirect'));
        }

        return redirect()->route('profile.index', [
            'id' => $userId == \Auth::user()->id ? null : $userId,
        ]);
    }

    /**
     * @param StoreTridiuumCredentials $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profileTridiuumStore(StoreTridiuumCredentials $request)
    {
        $userId = $request->get('user_id');

        \Bus::dispatchNow(new TridiuumCredentialStore($request->input()));

        if($request->expectsJson()) {
            return response()->json([
                'success' => true
            ], 201);
        }

        return redirect()->route('profile.index', [
            'id' => $userId == \Auth::user()->id ? null : $userId,
        ])->with('activeTab', 'tab_tridiuum');
    }

    public function profileTridiuumDelete(Request $request)
    {
        $userId = $request->get('user_id');
        User::findOrFail($userId)
            ->provider()
            ->update([
                'tridiuum_username'               => null,
                'tridiuum_password'               => null,
                'is_tridiuum_credentials_invalid' => null
            ]);

        return response()->json([
            'success' => true
        ], 200);
    }

    public function profileStatus()
    {
        $user = User::query()->where('id', '=', Auth::user()->id)->with(
            [
                'therapistSurvey.ageGroups',
                'therapistSurvey.typesOfClients',
                'therapistSurvey.practiceFocus',
            ])->first();

        return response(['status' => !!$user->isAdmin() || !!$user->isInsuranceAudit() || !!$user->therapistSurvey]);
    }

    public function getAvailableProvidersForPatient($id) {
        $providers = Provider::select(['id', 'provider_name'])
            ->whereRaw("providers.id NOT IN (
                    SELECT providers_id
                    FROM patients_has_providers
                    WHERE patients_id = $id AND chart_read_only = 0
                )
            ")
            ->orderBy('provider_name')
            ->get();

        return $providers;
    }

    public function allowEditingDocument(Request $request){
        
        $this->validate($request, [
            'documentId' => 'required|numeric|exists:patient_electronic_documents,id'
        ]);

        $note = PatientElectronicDocument::find($request->input('documentId'));
        $note->start_editing_date = Carbon::now();
        $note->save();
    }

}
