<?php

namespace App\Http\Controllers;

use App\DTO\OfficeAlly\AppointmentResource;
use App\Helpers\Sites\OfficeAlly\Enums\AppointmentStatuses;
use App\Jobs\Officeally\CreateVisits;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Models\Patient\Inquiry\PatientInquiryStage;
use App\Models\Patient\PatientTag;
use App\Option;
use App\PatientStatus;
use App\Repositories\NewPatientsCRM\PatientInquiry\PatientInquiryRepositoryInterface;
use App\Services\Ringcentral\RingcentralRingOut;
use App\Status;
use App\CallLog;
use App\Traits\Appointments\SendProviderNotification;
use Carbon\Carbon;
use App\Appointment;
use App\TridiuumSite;
use App\KaiserAppointment;
use Illuminate\Http\Request;
use App\AppointmentNotification;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\RetryJobQueueHelper;
use App\Http\Requests\Appointments\CancelAppointmentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangeOnPaperNote;
use App\Http\Requests\RingOut as RingOutRequest;
use App\Http\Requests\Appointments\PayCoPay;
use App\Http\Requests\Appointments\PastAppointment;
use App\Http\Requests\Appointments\ConfirmNotifications;
use App\Http\Requests\Appointments\Reschedule as RescheduleRequest;
use App\Http\Requests\Appointments\CreateVisit as CreateVisitRequest;
use App\Http\Requests\Appointments\Complete as CompleteRequest;
use App\Jobs\CalculateFeePerVisit;
use App\Jobs\Officeally\Retry\RetryAddPaymentToAppointment;
use App\Jobs\Patients\CalculatePatientBalance;
use App\Models\AppointmentRescheduleSubStatus;
use App\Models\Officeally\OfficeallyTransaction;
use App\Models\Officeally\OfficeallyTransactionPurpose;
use App\Models\Officeally\OfficeallyTransactionType;
use App\Office;
use App\OfficeRoom;
use App\Patient;
use App\PatientInsuranceProcedure;
use App\Models\Provider\ProviderSupervisor;
use App\PatientComment;
use App\PatientVisitFrequency;
use App\PatientVisitFrequencyChange;
use App\Repositories\Appointment\Model\AppointmentRepositoryInterface;
use App\Models\TreatmentModality;
use App\PatientInsurancePlanProcedure;

class AppointmentController extends Controller
{
    use SendProviderNotification;

    /**
     * @var AppointmentRepositoryInterface
     */
    protected $appointmentRepository;

    /**
     * AppointmentController constructor.
     * @param AppointmentRepositoryInterface $appointmentRepository
     */
    public function __construct(AppointmentRepositoryInterface $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    use PatientTrait, OfficeTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appointment $appointment)
    {
        //
    }

    protected function getAppointment($appId){

        $app = Appointment::find($appId);

        return $app;
    }

    public function getAppointmentPatient($appId){

        $app = $this->getAppointment($appId);

        $patient = $this->getPatient($app->patient->id);

        return $patient;
    }

    public function getAppointmentOffice($appId){

        $app = $this->getAppointment($appId);

        $office = $this->getOffice($app->offices_id);

        return $office;
    }

    public function completeAppointment(CompleteRequest $request) {
        $appointment = Appointment::where('id', $request->input('appointmentId'))
            ->with([
                'patient' => function($query) {
                    $query->select([
                        'patients.id',
                        'patients.patient_id',
                        'patients.primary_insurance_id',
                        'patients.insurance_plan_id'
                    ]);
                },
                'provider:id,is_test,officeally_id',
            ])
            ->first();

        $patient = $appointment->patient()->first();
        $notes = !empty($request->input('comment')) ? $request->input('comment') : $appointment->notes;
        
        $treatmentModality = TreatmentModality::find($request->input('reason_for_visit'));
        $isInitial = in_array($treatmentModality->id, TreatmentModality::initialEvaluationIds());
        $reasonForVisit = $treatmentModality->name;
        $visitLength = $treatmentModality->duration;

        $data = [
            'id' => isset($appointment->idAppointments) ? (int)$appointment->idAppointments : null,
            'date' => Carbon::createFromTimestamp($appointment->time),
            'officeId' => (int)$appointment->office->external_id,
            'patientId' => (int)$appointment->patient->patient_id,
            'reasonForVisit' => $reasonForVisit, 
            'providerId' => (int)$appointment->provider->officeally_id,
            'visitLength' => $visitLength,
            'resource' => new AppointmentResource([
                'type' => optional($appointment->officeRoom)->external_id ? AppointmentResource::TYPE_ROOM : 0,
                'id' => optional($appointment->officeRoom)->external_id ? (int)$appointment->officeRoom->external_id : 0,
            ]),
            'statusId' => AppointmentStatuses::COMPLETED,
            'notes' => $notes,
        ];

        RetryJobQueueHelper::dispatchRetryEditAppointment(Option::OA_ACCOUNT_2, $data, $appointment->id);

        $inquiry = $patient->activeInquiry()->exists() ? $patient->activeInquiry()->first() : null;
        $needToSetInquiryStageInitialAppointmentComplete = $inquiry && $inquiry->stage_id !== PatientInquiryStage::getInitialAppointmentCompleteId() && !$inquiry->getFirstCompletedAppointment() && optional($inquiry->getFirstActiveAppointment())->id === $appointment->id;
    
        $completedId = Status::getCompletedId();
        $appointment->appointment_statuses_id = $completedId;
        $appointment->new_status_id = $completedId;
        $appointment->custom_notes = $request->input('comment');
        $appointment->start_completing_date = Carbon::now();
        $appointment->reason_for_visit = $reasonForVisit;
        $appointment->visit_length = $visitLength;
        $appointment->is_initial = $isInitial;
        $appointment->treatment_modality_id = $request->input('reason_for_visit');
        $appointment->save();

        if ($needToSetInquiryStageInitialAppointmentComplete) {
            app()->make(PatientInquiryRepositoryInterface::class)->changeStage(
                $inquiry,
                [
                    'stage_id' => PatientInquiryStage::getInitialAppointmentCompleteId(),
                ],
                PatientInquiry::REASON_FOR_STAGE_CHANGE_COMPLETED_APPOINTMENT
            );
        }

        $inquiryCompleteAppointmentsСount = isset($inquiry) ? $inquiry->getCompletedAppointmentsCount() : 0;
        $needToSetInquiryStageFourAppointmentsComplete = $inquiry && $inquiry->stage_id !== PatientInquiryStage::getFourAppointmentsCompleteId() && $inquiryCompleteAppointmentsСount === PatientInquiryStage::COUNT_APPOINTMENTS_TO_SET_STAGE_FOUR_APPOINTMENTS_COMPLETE;
        if ($needToSetInquiryStageFourAppointmentsComplete) {
            app()->make(PatientInquiryRepositoryInterface::class)->changeStage(
                $inquiry,
                [
                    'stage_id' => PatientInquiryStage::getFourAppointmentsCompleteId(),
                ],
                PatientInquiry::REASON_FOR_STAGE_CHANGE_COMPLETED_APPOINTMENT
            );
        }

        $patient->detachTag(PatientTag::getTransferringId());
        $user = Auth::user();
        $oldVisitFrequencyId = $patient->visit_frequency_id;
        $newVisitFrequencyId = $request->input('visit_frequency_id');
        if ($oldVisitFrequencyId !== $newVisitFrequencyId) {
            $patient->update(['visit_frequency_id' => $newVisitFrequencyId]);

            PatientVisitFrequencyChange::create([
                'patient_id' => $patient->id,
                'old_visit_frequency_id' => $oldVisitFrequencyId,
                'new_visit_frequency_id' => $newVisitFrequencyId,
                'changed_by' => $user->id,
                'comment' => $request->has('change_visit_frequency_comment') ? e($request->input('change_visit_frequency_comment')) : null,
            ]);

            $patientCommentData = [
                'patient_id' => $patient->id,
                'admin_id' => $user->isAdmin() ? $user->id : null,
                'provider_id' => $user->isProvider() ? $user->provider_id : null,
                'comment' => $request->has('change_visit_frequency_comment') ? e($request->input('change_visit_frequency_comment')) : null,
                'comment_type' => PatientComment::CHANGE_VISIT_FREQUENCY_TYPE,
                'metadata' => [
                    'old_value' => $oldVisitFrequencyId ? PatientVisitFrequency::getNameById($oldVisitFrequencyId) : null,
                    'new_value' => PatientVisitFrequency::getNameById($newVisitFrequencyId)
                ]
            ];
            PatientComment::create($patientCommentData);
        }

        if (!$patient->is_test || $appointment->provider->is_test) {
            $this->appointmentRepository->createVisitForAppointment($appointment);
        }

        $hasPastUnresolvedAppointments = Appointment::getBasePastAppointmentsQuery()->exists();
        if (!$hasPastUnresolvedAppointments) {
            session(['has_unresolved_past_appointments' => false]);
        }

        if ($patient->status_id === PatientStatus::getDischargedId()) {
            $this->changeDischargedToActive($patient, Carbon::createFromTimestamp($appointment->time));
        } else if ($patient->status_id === PatientStatus::getArchivedId()) {
            PatientStatus::changeStatusAutomatically($patient->id, 'archived_to_active');
        }

        return response([], 201);
    }

    public function rescheduleAppointment(RescheduleRequest $request, Appointment $appointment) {
        $this->appointmentRepository->rescheduleAppointment($request->validated(), $appointment);

        return response([], 201);
    }

    public function cancelAppointment(CancelAppointmentRequest $request) {
        $formData = $request->validated();

        $statusName = Status::find($formData['status'])->external_id;

        $appointment = Appointment::where('id', $formData['appointmentId'])
            ->with([
                'patient:id,patient_id',
                'provider' => function ($withQuery) {
                    $withQuery->withTrashed();
                },
            ])
            ->first();

        $chargeForCancellation = $formData['charge_for_cancellation'] ?? 0;
        if ($chargeForCancellation > 0) {
            $this->appointmentRepository->createLateCancellationTransaction($appointment, $chargeForCancellation);
        }

        $patient = $appointment->patient;
        $comment = $formData['comment'];

        $data = [
            'id' => isset($appointment->idAppointments) ? (int)$appointment->idAppointments : null,
            'date' => Carbon::createFromTimestamp($appointment->time),
            'officeId' => (int)$appointment->office->external_id,
            'patientId' => (int)$appointment->patient->patient_id,
            'reasonForVisit' => $appointment->reason_for_visit,
            'providerId' => (int)$appointment->provider->officeally_id,
            'visitLength' => $appointment->visit_length,
            'resource' => new AppointmentResource([
                'type' => optional($appointment->officeRoom)->external_id  ? AppointmentResource::TYPE_ROOM : 0,
                'id' => optional($appointment->officeRoom)->external_id  ? (int)$appointment->officeRoom->external_id : 0,
            ]),
            'statusId' => (int)$statusName,
            'notes' => $comment,
        ];

        RetryJobQueueHelper::dispatchRetryEditAppointment(Option::OA_ACCOUNT_2, $data, $appointment->id);

        $inquiry = $patient->activeInquiry()->exists() ? $patient->activeInquiry()->first() : null;
        $needToSetInquiryStageInProgress = $inquiry && $inquiry->stage_id !== PatientInquiryStage::getInProgressId() && !$inquiry->getFirstCompletedAppointment() && optional($inquiry->getFirstActiveAppointment())->id === $appointment->id;

        $appointment->appointment_statuses_id = $formData['status'];
        $appointment->new_status_id = $formData['status'];
        $appointment->custom_notes = $comment;
        $appointment->start_completing_date = Carbon::now();
        $appointment->patient_requested_cancellation_at = $formData['patient_requested_cancellation_at'] ?? null;
        $appointment->save();
        $appointment->removeMeetings();

        if ($needToSetInquiryStageInProgress) {
            app()->make(PatientInquiryRepositoryInterface::class)->changeStage(
                $inquiry,
                [
                    'stage_id' => PatientInquiryStage::getInProgressId(),
                ],
                PatientInquiry::REASON_FOR_STAGE_CHANGE_CANCELED_APPOINTMENT
            );
        }
    
        $hasPastUnresolvedAppointments = Appointment::getBasePastAppointmentsQuery()->exists();
        if(!$hasPastUnresolvedAppointments) {
            session(['has_unresolved_past_appointments' => false]);
        }
        
        return response([], 201);
    }

    public function getCancelStatuses() {
        return response(Status::getStatusesLikeCancel());
    }

    public function getOtherCancelStatuses() {
        $statuses = Status::getNewCancelStatuses();

        if (!Auth::user()->isAdmin()) {
            $statuses = array_filter($statuses, function ($status) {
                $cancelledByOfficeId = Status::getCancelledByOfficeId();
                return $status['id'] !== $cancelledByOfficeId;
            });
        }

        return response(array_values($statuses));
    }

    public function getRescheduleStatuses()
    {
        return response()->json(Status::getRescheduleStatuses());
    }

    public function getRescheduleSubStatuses()
    {
        return response()->json(AppointmentRescheduleSubStatus::all());
    }

    /**
     * @param PayCoPay $request
     */
    public function payCoPay(PayCoPay $request)
    {
        $data = $request->validated();
        $appointmentId = $data['appointment_id'];
        $appointment = Appointment::find($appointmentId);
        $patientId = $appointment->patients_id;
        $paymentAmount = (float) $data['payment_amount'];
        $method = $data['method'] ?? null;
        
        if (!$paymentAmount) {
            return response([
                'success' => true,
            ], 200);
        }
        
        $checkNo = '';
        $paymentMethod = null;
        switch ($method) {
            case OfficeallyTransactionType::CASH_PAYMENT_METHOD;
                $paymentMethod = OfficeallyTransactionType::getCashType();
                break;
            case OfficeallyTransactionType::CHECK_PAYMENT_METHOD:
                $checkNo = $data['check_no'];
                $paymentMethod = OfficeallyTransactionType::getCheckType();
                break;
            case OfficeallyTransactionType::CREDIT_CARD_PAYMENT_METHOD:
                $paymentMethod = OfficeallyTransactionType::getCreditCardType();
                break;
        }

        $transactionPurpose = OfficeallyTransactionPurpose::find($data['transaction_purpose_id']);
        $description = $transactionPurpose->description;

        $transaction = OfficeallyTransaction::create([
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId,
            'transaction_type_id' => $paymentMethod->id,
            'transaction_purpose_id' => $transactionPurpose->id,
            'payment_amount' => $paymentAmount * 100,
            'applied_amount' => 0,
            'user_id' => auth()->id(),
            'transaction_date' => Carbon::now()
        ]);

        $account = Option::OA_ACCOUNT_2;
        $officeAllyHelper = new \App\Helpers\Sites\OfficeAlly\OfficeAllyHelper($account);

        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        if ($appointment->idAppointments) {
            try {
                $payment = $officeAllyHelper->addPaymentToAppointment($paymentAmount, $appointment->idAppointments, $appointment->patient->patient_id,  $appointment->provider()->withTrashed()->first()->officeally_id, $appointment->office->external_id, $paymentMethod->officeally_id, $description, (string)$checkNo);
                
                if (isset($payment)) {
                    $appointment->payed = true;
                    $appointment->save();
    
                    $transaction->update([
                        'external_id' => $payment['id'],
                        'transaction_date' => Carbon::createFromFormat('m/d/Y', $payment['cell'][1])
                    ]);
                }
            } catch (OfficeallyAuthenticationException $e) {
                $job = (new RetryAddPaymentToAppointment($appointment->id, $transaction->id, $account, $paymentAmount, $paymentMethod->officeally_id, $description, (string)$checkNo))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryAddPaymentToAppointment($appointment->id, $transaction->id, $account, $paymentAmount, $paymentMethod->officeally_id, $description, (string)$checkNo))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }

        \Bus::dispatchNow(new CalculatePatientBalance([$patientId]));
        
        return response([
            'success' => true,
        ], 200);
    }

    public function changeOnPaperNote(ChangeOnPaperNote $request) {
        $success = Appointment::where('id', '=', $request->input('appointment_id'))
            ->update(['note_on_paper' => $request->input('on_paper')]);
        return response([
            'success' => $success,
        ]);
    }

    public function getTimeByDate(Request $request) {

        $this->validate($request, [
            'date' => 'required',
            'patient_id' => 'required|numeric|exists:patients,id'
        ]);

        $date = Carbon::createFromFormat('m/d/Y', $request->input('date'));

        if($date === false) {
            return response();
        }

        $appointmentTime = Appointment::select('time')
            ->whereRaw("
                DATE(DATE_FORMAT(FROM_UNIXTIME(time), '%Y-%m-%d')) = DATE('$date')
            ")->where('patients_id', $request->patient_id)
            ->first();
        if(!is_null($appointmentTime)) {
            $appointmentTime = Carbon::createFromTimestamp($appointmentTime->time)->format('h:i A');
            return $appointmentTime;
        }
        return '';
    }

    public function uncheckPaperNotes(Request $request) {
        $this->validate($request, [
            'patientId' => 'required|numeric|exists:patients,id'
        ]);

        $status = Appointment::where('patients_id', $request->patientId)
            ->where('note_on_paper', true)
            ->update(['note_on_paper' => false]);
        return response([
            'updated_count' => $status,
        ]);
    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function getCompletedAppointments(Request $request) {
        if (Auth::user()->isSecretary()) {
            abort(403);
        }

        if (!$request->has('statuses')) {
            $statuses = [7];
        } else if(!count($request->statuses)) {
            $statuses = [0];
        } else {
            $statuses = $request->statuses;
        }

        $statusesFilter = Status::select(['id', 'status'])
            ->orderBy('status')->get();

        foreach($statusesFilter as $status) {
            $status->selected = in_array($status->id, $statuses);
        }

        $appointments = Appointment::query()
            ->select([
                'appointments.id',
                'appointments.patients_id',
                'appointments.appointment_statuses_id',
                'appointments.is_creating_visit_inprogress',
                'appointments.idAppointments AS officeally_appt_id',
                'appointments.time',
                'appointments.new_status_id',
                'appointments.is_warning',
                'appointments.error_message',
                'appointments.reason_for_visit',
                'appointments.appointment_statuses_id', 
                'appointments.reschedule_sub_status_id',
                'appointments.providers_id',
                'appointments.custom_notes AS custom_notes',
                'appointments.start_completing_date',
                'appointments.created_at',
                'appointments.patient_requested_cancellation_at',
                DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"),
                'patients.id AS patient_id',
                'patients.patient_id AS external_patient_id',
                'patients.primary_insurance AS primary_insurance',
                'patient_insurances_plans.name AS insurance_plan',
                'providers.provider_name',
                DB::raw('MONTH(FROM_UNIXTIME(appointments.time)) AS appt_date_month'),
                DB::raw('YEAR(FROM_UNIXTIME(appointments.time)) AS appt_date_year'),
                DB::raw('DATE(FROM_UNIXTIME(appointments.time)) AS appt_date'),
                DB::raw('FROM_UNIXTIME(appointments.time) AS appt_date_time'),
            ])
            ->with(['status', 'rescheduleSubStatus', 'patient', 'patient.insurancePlan', 'patientTemplates' => function($query) {
                $query->orderBy('position');
            }])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->leftJoin('patient_insurances_plans', 'patients.insurance_plan_id', '=', 'patient_insurances_plans.id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->where('patients.is_test', 0)
            ->whereIn('appointment_statuses_id', $statuses)
            ->when($request->has('selected_provider') && $request->selected_provider > 0, function($query) use (&$request) {
                $query->where('appointments.providers_id', $request->selected_provider);
            })
            ->when($request->has('only_60_min_sessions') && $request->only_60_min_sessions, function ($query) {
                $query
                    ->leftJoin('treatment_modalities', 'treatment_modalities.id', '=', 'appointments.treatment_modality_id')
                    ->leftJoin('patient_insurances_procedures', 'treatment_modalities.insurance_procedure_id', '=', 'patient_insurances_procedures.id')
                    ->where('patient_insurances_procedures.id', PatientInsuranceProcedure::get60MinProcedureId());
            })
            ->orderBy('time', 'desc');

        $filterType = 2;
        if ($request->filled('selected_filter_type')) {
            $filterType = $request->selected_filter_type;
        }

        $month = $request->filled('month') ? Carbon::createFromFormat('d F Y', $request->month) : Carbon::now();
        $dateFrom = $request->filled('date_from') ? Carbon::createFromFormat('m/d/Y', $request->date_from) : null;
        $dateTo = $request->filled('date_to') ? Carbon::createFromFormat('m/d/Y', $request->date_to) : null;

        switch($filterType) {
            case 1:
                if(is_null($dateFrom)) {
                    $dateFrom = Carbon::now();
                }
                $appointments = $appointments->havingRaw("appt_date = date('" . $dateFrom->format('Y-m-d') . "')");
                break;
            case 2:
                if(is_null($dateFrom)) {
                    $dateFrom = Carbon::now()->subWeek();
                }
                if(is_null($dateTo)) {
                    $dateTo = Carbon::now();
                }
                $appointments = $appointments->havingRaw("appt_date >= date('{$dateFrom->toDateString()}') AND appt_date <= date('{$dateTo->toDateString()}')");
                break;
            case 3:
                $appointments = $appointments->havingRaw("appt_date_month = {$month->month} AND appt_date_year = {$month->year}");
                break;
        }

        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();
        $dataset = [];
        $visitInprogressCount = 0;
        $appointments = $appointments->get();

        foreach ($appointments as $appointment) {
            if ($appointment->is_creating_visit_inprogress) {
                $visitInprogressCount++;
            }

            $appointment->allow_create_visit = ($appointment->appointment_statuses_id == $completedId &&
                !$appointment->is_creating_visit_inprogress &&
                $appointment->new_status_id != $visitCreatedId &&
                !$appointment->error_message) ||
                ($appointment->error_message && $appointment->is_warning);

            $providerSupervisor = ProviderSupervisor::getSupervisorForDate($appointment->providers_id, Carbon::createFromTimestamp($appointment->time));
            $appointment->supervisor = isset($providerSupervisor) ? $providerSupervisor->supervisor()->withTrashed()->first() : null;

            $dataset[$appointment->appt_date]['dataset'][] = $appointment;
            $dataset[$appointment->appt_date]['date'] = Carbon::createFromFormat('Y-m-d', $appointment->appt_date)->format('m/d/Y');
        }

        $month = null;
        if ($request->filled('month')) {
            $month = Carbon::createFromFormat('d F Y', $request->month)->format('d F Y');
        }

        if (is_null($dateFrom)) {
            $dateFrom = Carbon::now();
        }
        if (is_null($dateTo)) {
            $dateTo = Carbon::now();
        }

        $response = [
            'appointments' => collect($dataset),
            'dateFrom' => $dateFrom->format('m/d/Y'),
            'dateTo' => $dateTo->format('m/d/Y'),
            'month' => $month,
            'selectedFilterType' => $request->filled('selected_filter_type') ? $request->selected_filter_type : 2,
            'statusesFilter' => $statusesFilter,
            'visitInprogressCount' => $visitInprogressCount,
        ];

        if ($request->expectsJson()) {
            return response($response);
        }

        return view('dashboard.appointments.completed', $response);
    }

    public function viewKaiserAppointments(Request $request)
    {
        return view('dashboard.appointments.kaiser');
    }

    public function getKaiserAppointments(Request $request)
    {
        $dateTo = $request->input('date_to');
        $dateFrom = $request->input('date_from');
        $month = $request->input('month');
        $status = $request->input('status');
        $site = $request->input('site');
        $providerId = $request->input('provider_id');

        $appointments = KaiserAppointment::leftJoin('providers', 'kaiser_appointments.provider_id', '=', 'providers.id')
            ->select(
                'kaiser_appointments.*',
                'providers.provider_name as provider_name',
                DB::raw('CAST(start_date as time) as time_appt'),
                DB::raw('DATE(kaiser_appointments.start_date) AS appt_date'),
                DB::raw('DATE(kaiser_appointments.created_at) AS parsed_date'),
                'tridiuum_sites.tridiuum_site_name'
            )
            ->leftJoin('tridiuum_sites', 'kaiser_appointments.site_id', '=', 'tridiuum_sites.id')
            ->when($dateTo, function($query, $dateTo) use ($dateFrom) {
                return $query->whereBetween('kaiser_appointments.created_at', [Carbon::createFromFormat('m/d/Y', $dateFrom), Carbon::createFromFormat('m/d/Y', $dateTo)]);
            })
            ->when($month, function($query, $month){
                $from = (new Carbon($month))->startOfMonth();
                $to = (new Carbon($month))->endOfMonth();
                return $query->whereBetween('kaiser_appointments.created_at', [$from, $to]);
            })
            ->when($dateFrom, function($query, $dateFrom) use ($dateTo) {
                if(!$dateTo) {
                    $from = (new Carbon($dateFrom))->startOfDay();
                    $to = (new Carbon($dateFrom))->endOfDay();
                    return $query->whereBetween('kaiser_appointments.created_at', [$from, $to]);
                }
            })
            ->when($status, function($query, $status) {
                if($status == 3) {
                    return $query->where('kaiser_appointments.status', null);
                }
                return $query->where('kaiser_appointments.status', '=', $status);
            })
            ->when($site, function($query, $site) {
                return $query->whereIn('kaiser_appointments.site_id', $site);
            })
            ->when($providerId, function($query, $providerId) {
                return $query->where('kaiser_appointments.provider_id', '=', $providerId);
            })
            ->orderBy('start_date', 'asc')
            ->with('secretary.meta')
            ->get();
            
        $collection = $appointments
            // filter kaiser appts. with patients that already had visits before
            ->filter(function ($kaiserAppointment) {
                // skip already resolved appointments
                if (isset($kaiserAppointment->status)) {
                    return true;
                }

                return !$kaiserAppointment->hasPreviousVisitCreated();
            })
            ->groupBy('parsed_date')
            ->toArray();
        
        krsort($collection);

        return response()->json($collection);
    }

    public function getKaiserAppointmentsDetail($id)
    {
        $appointment = KaiserAppointment::query()
            ->leftJoin('tridiuum_sites', 'kaiser_appointments.site_id', '=', 'tridiuum_sites.id')
            ->select('kaiser_appointments.*', 'tridiuum_sites.tridiuum_site_name')
            ->with(['callLogs.user.meta', 'patient', 'provider'])
            ->findOrFail($id);
        return $appointment;
    }

    public function updateKaiserAppointment($id, Request $request)
    {
        $appointment = KaiserAppointment::query()
            ->leftJoin('tridiuum_sites', 'kaiser_appointments.site_id', '=', 'tridiuum_sites.id')
            ->select(
                'kaiser_appointments.*',
                'tridiuum_sites.tridiuum_site_name'
            )
            ->with(['callLogs', 'patient', 'provider'])->findOrFail($id);
        $payload = $request->all();
        $payload['user_id'] = \Auth::id();
        $appointment->update($payload);
        return response()->json($appointment);
    }

    public function getKaiserSites()
    {
        return response()->json(TridiuumSite::all());
    }
    
    /**
     * @deprecated to be deleted. New API method \App\Http\Controllers\Api\Ringcentral\RingOutController::storeForAppointment
     * @see \App\Http\Controllers\Api\Ringcentral\RingOutController::storeForAppointment
     * @param RingOutRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ringout(RingOutRequest $request)
    {
        $appointment = KaiserAppointment::findOrFail($request->input('appointment_id'));
        $ringOut = new RingcentralRingOut();
        $call = $ringOut->store((string)$request->get('phone_from'), (string)$request->get('phone_to'), (bool)$request->get('play_prompt', true));
        $payload = array_merge(
            $request->all(),
            [
                'patient_id' => $appointment->patient_id,
                'ring_central_call_id' => $call['id'],
                'user_id' => auth()->user()->id,
                'status_text' => CallLog::INIT_STATUS
            ]
        );
        $callLog = CallLog::create($payload);
        return response()->json($callLog);
    }

    public function callLogUpdate($id, Request $request)
    {
        $callLog = CallLog::findOrFail($id);
        $callLog->update($request->all());
        $appointment = KaiserAppointment::with(['callLogs', 'patient', 'provider'])->findOrFail($callLog->appointment_id);
        return response()->json($appointment);
    }

    public function createVisit(CreateVisitRequest $request) {
        $completedId = Status::getCompletedId();
        $visitCreatedId = Status::getVisitCreatedId();

        Appointment::query()
            ->whereIn('id', array_pluck($request->input('appointments'), 'id'))
            ->where('is_creating_visit_inprogress', 0)
            ->where('appointment_statuses_id', $completedId)
            ->where(function($query) use ($visitCreatedId) {
                $query->whereNull('new_status_id')
                    ->orWhere('new_status_id', '!=', $visitCreatedId);
            })
            ->update([
                'is_creating_visit_inprogress' => true,
                'start_creating_visit' => Carbon::now(),
            ]);
        
        $job = with(new CreateVisits($request->input('appointments')))->onQueue('officeally-billing');
        dispatch($job);

        return response([], 201);
    }

    public function getInprogressVisitCount() {
        $count = Appointment::where('is_creating_visit_inprogress', true)
            ->count();

        return $count;
    }

    public function getNotifications()
    {
        return auth()->user()->provider ? auth()->user()->provider->appointmentNotifications()
            ->join('appointments', 'appointments.id', 'appointment_notifications.appointment_id')
            ->orderBy('appointments.time')
            ->with('appointment.patient')
            ->where('status', AppointmentNotification::STATUS_NEW)
            ->whereNull('appointments.deleted_at')
            ->get()
            ->transform(function(AppointmentNotification $item) {
                $item->appointment->setAttribute('start_time', Carbon::createFromTimestamp($item->appointment->time)->toDateTimeString());

                return $item;
            }) : collect();
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmNotifications(ConfirmNotifications $request)
    {
        $notifications = AppointmentNotification::whereIn('appointment_id', $request->get('notifications'))
            ->update([
                'status' => AppointmentNotification::STATUS_CONFIRMED
            ]);

        return response()->json([
            'status' => 1
        ]);
    }

    /**
     * Get past appointments
     *
     * @param PastAppointment $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPastAppointments(PastAppointment $request)
    {
        $data = Appointment::getBasePastAppointmentsQuery()
            ->select([
                'appointments.*',
                'patients.first_name',
                'patients.last_name',
                'patient_statuses.hex_color'
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->get();
        $isRedirect = session('has_unresolved_past_appointments', false);
        if($data->isEmpty()) {
            session(['has_unresolved_past_appointments' => false]);
            $isRedirect = false;
        }
        
        return response()->json([
            'appointments' => $data->groupBy(function($date) {
                return Carbon::createFromTimestamp($date->time)->toDateString();
            }),
            'provider' => auth()->user()->provider,
            'is_redirect' => $isRedirect,
        ]);
    }

    public function getFeePerVisit(Appointment $appointment, TreatmentModality $treatmentModality)
    {
        $feePerVisit = \Bus::dispatchNow(new CalculateFeePerVisit($appointment->patient, $appointment->provider, $treatmentModality));
        return response()->json(['fee_per_visit' => $feePerVisit]);
    }
}
