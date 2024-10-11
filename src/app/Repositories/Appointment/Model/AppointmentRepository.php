<?php

namespace App\Repositories\Appointment\Model;

use App\Appointment;
use App\Events\NeedsWriteSystemComment;
use App\Exceptions\EmptyGoogleAccountException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\GoogleMeeting;
use App\Models\UphealMeeting;
use App\Office;
use App\OfficeRoom;
use App\Option;
use App\PatientVisit;
use App\Models\Billing\BillingPeriod;
use App\Models\Provider\SalaryTimesheetLateCancellation;
use App\Models\LateCancellationTransaction;
use App\DTO\OfficeAlly\AppointmentResource;
use App\Enums\VisitType;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\RetryJobQueueHelper;
use App\Helpers\Sites\OfficeAlly\Enums\AppointmentStatuses;
use App\Patient;
use App\Provider;
use App\Models\Provider\ProviderSupervisor;
use App\Status;
use App\Models\Provider\SalaryTimesheet;
use App\Models\TreatmentModality;
use App\PatientDocument;
use App\PatientDocumentType;
use App\Traits\Appointments\GoogleCalendar;
use App\Traits\Appointments\SendProviderNotification;
use App\Traits\Appointments\VideoSession;
use App\Traits\Patient\PatientProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\UphealHelper;
use App\Jobs\Officeally\Retry\RetryCreateAppointment;
use App\Jobs\Officeally\Retry\RetryDeleteAppointment;
use App\Services\Twilio\TwilioLookup;
use Twilio\Exceptions\RestException;
use App\Exceptions\PhoneIsUnableToReceiveSmsException;
use App\Jobs\Comments\ParseCommentMentions;
use App\Jobs\Salary\SyncSalaryData;
use App\Jobs\Patients\CalculatePatientBalance;
use App\PatientComment;
use App\PatientInsuranceProcedure;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    use SendProviderNotification, GoogleCalendar, VideoSession, PatientProvider;

    const VISIT_TYPE_IN_VIRTUAL = 'virtual';
    const VISIT_TYPE_IN_PERSON = 'in_person';

    /**
     * @var array
     */
    protected $filterData;

    /**
     * @var Builder
     */
    protected $searchQuery;
    
    public function isProgressNoteMissing(Appointment $appointment): bool
    {
        if(
            ($appointment->note_on_paper && !$appointment->is_initial) || 
//            $appointment->is_initial ||
            $appointment->initial_assessment_id ||
            $appointment->patientNote()->where('provider_id', $appointment->providers_id)->onlyFinalized()->exists()
        ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @param array $data
     * @return AppointmentRepository
     */
    protected function setFilterData(array $data): AppointmentRepository
    {
        $this->filterData = $data;

        return $this;
    }

    /**
     * @return AppointmentRepository
     */
    protected function setSearchQuery(): AppointmentRepository
    {
        $this->searchQuery = Appointment::query()
            ->select([
                'appointments.id', 'appointments.time', 'appointments.visit_length', 'appointments.reason_for_visit',
                'appointments.offices_id', 'appointments.providers_id', 'appointments.office_room_id', 'appointments.custom_notes',
                'appointments.appointment_statuses_id', 'appointments.reschedule_sub_status_id', 'appointments.start_completing_date',
                'appointments.created_at', 'appointments.patient_requested_cancellation_at',
                'patients.id as patient_id', 'patients.first_name', 'patients.last_name', 'patients.middle_initial',
                DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"), 'patients.primary_insurance',
                'offices.office', 'office_rooms.name as office_room_name', 'providers.provider_name',
                DB::raw("FROM_UNIXTIME(rescheduled_appointment.time, '%m/%d/%y') as rescheduled_appointment_date"),
            ])
            ->with(['status', 'rescheduleSubStatus'])
            ->leftJoin('patients', 'appointments.patients_id', '=', 'patients.id')
            ->leftJoin('providers', 'appointments.providers_id', '=', 'providers.id')
            ->leftJoin('offices', 'appointments.offices_id', '=', 'offices.id')
            ->leftJoin('office_rooms', 'appointments.office_room_id', '=', 'office_rooms.id')
            ->leftJoin('appointments as rescheduled_appointment', 'rescheduled_appointment.rescheduled_appointment_id', '=', 'appointments.id')
            ->groupBy('appointments.id');

        return $this;
    }

    /**
     * @param array $data
     * @return Collection|\Illuminate\Support\Collection
     */
    public function all(array $data): Collection
    {
        $this->setFilterData($data)
            ->setSearchQuery()
            ->applyFilters()
            ->setSort();

        return $this->searchQuery->get();
    }

    /**
     * @return AppointmentRepository
     */
    protected function applyFilters(): AppointmentRepository
    {
        $this->applyDateFilter()
            ->applyProviderFilter()
            ->applyOfficeFilter()
            ->applyVisitTypeFilter()
            ->applyAppointmentStatusesFilter();

        return $this;
    }

    /**
     * @return AppointmentRepository
     */
    protected function applyDateFilter(): AppointmentRepository
    {
        if (!empty($this->filterData['date'])) {
            $this->searchQuery
                ->where('appointments.time', '>=', Carbon::parse($this->filterData['date'])->startOfDay()->timestamp)
                ->where('appointments.time', '<=', Carbon::parse($this->filterData['date'])->endOfDay()->timestamp);
        } else {
            $this->searchQuery
                ->where('appointments.time', '>=', Carbon::now()->startOfDay()->timestamp)
                ->where('appointments.time', '<=', Carbon::now()->endOfDay()->timestamp);
        }

        return $this;
    }

    /**
     * @return AppointmentRepository
     */
    protected function applyProviderFilter(): AppointmentRepository
    {
        $providerId = Auth::user()->isOnlyProvider() ? Auth::user()->provider_id : $this->filterData['providers_id'] ?? null;

        if (!empty($providerId)) {
            $this->searchQuery->where('appointments.providers_id', '=', $providerId);
        }

        return $this;
    }

    /**
     * @return AppointmentRepository
     */
    protected function applyOfficeFilter(): AppointmentRepository
    {
        if (!empty($this->filterData['offices_id']) && is_array($this->filterData['offices_id'])) {
            $this->searchQuery->whereIn('appointments.offices_id', $this->filterData['offices_id']);
        }

        return $this;
    }

    /**
     * @return AppointmentRepository
     */
    protected function applyVisitTypeFilter(): AppointmentRepository
    {
        if (!empty($this->filterData['visit_type']) && is_array($this->filterData['visit_type'])) {
            if (array_search(self::VISIT_TYPE_IN_VIRTUAL, $this->filterData['visit_type']) !== false &&
                array_search(self::VISIT_TYPE_IN_PERSON, $this->filterData['visit_type']) === false) {
                $this->searchQuery->join('treatment_modalities', 'treatment_modalities.id', '=', 'appointments.treatment_modality_id')
                    ->where('treatment_modalities.is_telehealth', true);
            } elseif (array_search(self::VISIT_TYPE_IN_VIRTUAL, $this->filterData['visit_type']) === false &&
                array_search(self::VISIT_TYPE_IN_PERSON, $this->filterData['visit_type']) !== false) {
                $this->searchQuery->join('treatment_modalities', 'treatment_modalities.id', '=', 'appointments.treatment_modality_id')
                    ->where('treatment_modalities.is_telehealth', false);
            }
        }

        return $this;
    }

    protected function applyAppointmentStatusesFilter(): AppointmentRepository
    {
        if (!empty($this->filterData['appointment_statuses'])) {
            $this->searchQuery->whereIn('appointments.appointment_statuses_id', $this->filterData['appointment_statuses']);
        }

        return $this;
    }

    /**
     * @return AppointmentRepository
     */
    protected function setSort(): AppointmentRepository
    {
        $this->searchQuery->orderBy('appointments.time')
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name');

        return $this;
    }

    /**
     * @param array $attributes
     * @return Appointment|null
     */
    public function create(array $attributes): ?Appointment
    {
        if (data_get($attributes, 'phone') && data_get($attributes, 'send_telehealth_link_via_sms')) {
            try {
                TwilioLookup::validatePhone(data_get($attributes, 'phone'));
            } catch (RestException | PhoneIsUnableToReceiveSmsException $e) {
                // exception is handled in App\Exceptions\Handler
                throw new PhoneIsUnableToReceiveSmsException(data_get($attributes, 'phone'));
            }
        }

        if (Auth::user()->isOnlyProvider()) {
            return $this->createProviderAppointment($attributes);
        }

        return $this->createAppointment($attributes);
    }

    /**
     * @param array $attributes
     * @return Appointment|null
     */
    protected function createProviderAppointment(array $attributes): ?Appointment
    {
        $appointment = $this->createAppointment($attributes);
        $patient = Patient::whereId($attributes['patient_id'])->first();
        $daysBetweenVisits = $patient->getDaysBetweenVisits();

        if (!empty($attributes['repeat']) && $daysBetweenVisits > 0) {
            $prevAppointmentDate = isset($appointment) ? 
                Carbon::createFromTimestamp($appointment->time) : 
                Carbon::parse($attributes['date'] . ' ' . $attributes['time']);
            
            for ($i = 1; $i <= $attributes['repeat']; $i++) {
                $prevAppointmentDate->addDays($daysBetweenVisits);
                $attributes['date'] = $prevAppointmentDate->toDateString();
                $attributes['telehealth_notification_date'] = Carbon::parse($attributes['date'] . ' ' . $attributes['time'])->subHour()->format('m/d/Y h:i A');
                $attributes['notes'] = '';

                $appointmentCount = $patient->appointments()
                    ->whereRaw('time BETWEEN ' . $prevAppointmentDate->startOfDay()->timestamp
                    . ' AND ' . $prevAppointmentDate->endOfDay()->timestamp)
                    ->count();

                if ($appointmentCount === 0) {
                    $this->createAppointment($attributes);
                }
            }
        }

        return $appointment;
    }
    
    /**
     * @param array $attributes
     *
     * @return Appointment|null
     */
    public function createAppointment(array $attributes): ?Appointment
    {
        $provider = Provider::withTrashed()->whereId($attributes['provider_id'])->first();
        $patient = Patient::whereId($attributes['patient_id'])->first();
        $office = isset($attributes['office_id'])
            ? Office::whereId($attributes['office_id'])->first()
            : Office::getEncinoOffice();
        if (isset($attributes['office_room'])) {
            $room = OfficeRoom::whereId($attributes['office_room'])->first();
        } else {
            $room = null;
        }

        $treatmentModality = TreatmentModality::find(data_get($attributes, 'reason_for_visit'));
        $isInitial = in_array($treatmentModality->id, TreatmentModality::initialEvaluationIds());
        $reasonForVisit = $treatmentModality->name;
        $visitLength = $attributes['visit_length'] ?? $treatmentModality->duration;

        $scheduledBy = '';
        if(auth()->user()) {
            $scheduledBy = auth()->user()->provider ? auth()->user()->provider->provider_name : auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
        }

        $appointmentId = null;
        $appointment = Appointment::create([
            'time' => strtotime($attributes['date'] . ' ' . $attributes['time']),
            'idAppointments' => $appointmentId,
            'visit_length' => $visitLength,
            'notes' => $attributes['notes'] ?? null,
            'reason_for_visit' => $reasonForVisit,
            'is_initial' => $isInitial,
            'treatment_modality_id' => $attributes['reason_for_visit'],
            'sheldued_by' => $scheduledBy,
            'date_created' => Carbon::now()->format('M d, Y g:i A'),
            'not_found_count' => 0,
            'providers_id' => $attributes['provider_id'],
            'patients_id' => $attributes['patient_id'],
            'offices_id' => $office->id,
            'office_room_id' =>  optional($room)->id,
            'appointment_statuses_id' => Status::getActiveId(),
        ]);

        $account = Option::OA_ACCOUNT_1;
        $officeAlly = new OfficeAllyHelper($account);

        $data = new \App\DTO\OfficeAlly\Appointment([
            'date' => Carbon::parse($attributes['date'] . ' ' . $attributes['time']),
            'officeId' => (int)$office->external_id,
            'patientId' => (int)$patient->patient_id,
            'reasonForVisit' => $reasonForVisit,
            'providerId' =>  (int)$provider->officeally_id,
            'visitLength' => intval($visitLength),
            'resource' => new AppointmentResource([
                'type' => optional($room)->external_id ? AppointmentResource::TYPE_ROOM : 0,
                'id' => optional($room)->external_id ? (int)$room->external_id : 0,
            ]),
            'statusId' => AppointmentStatuses::ACTIVE,
            'notes' => $attributes['notes'] ?? null,
        ]);

        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        if ($patient->patient_id) {
            try {
                $appointmentId = $officeAlly->createAppointment($data);
            } catch (OfficeallyAuthenticationException $e) {
                $job = (new RetryCreateAppointment($account, $data, $appointment->id, $patient->id))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryCreateAppointment($account, $data, $appointment->id, $patient->id))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }

        if ($appointmentId) {
            $appointment->idAppointments = (int)$appointmentId;
            $appointment->save();
        }

        $this->connectProvider($provider, $patient);

        $appointmentTime = Carbon::createFromTimestamp($appointment->time);

        $isFirstPatientAppointmentWithProvider = Appointment::query()
            ->where('patients_id', $patient->id)
            ->where('providers_id', $provider->id)
            ->whereBetween('time', [
                $appointmentTime->copy()->subMonths(6)->startOfDay()->timestamp,
                $appointment->time - 1
            ])
            ->doesntExist();

        if ($isFirstPatientAppointmentWithProvider && $appointmentTime->isFuture()) {
            $this->sendFirstAppointmentNotifications($appointment, $provider, $patient, $office);
        }

        $this->setPatientEmailIfEmpty($patient, $attributes['email'] ?? null);

        if ($attributes['visit_type'] === VisitType::VIRTUAL && (
            data_get($attributes, 'send_telehealth_link_via_email') ||
            data_get($attributes, 'send_telehealth_link_via_secondary_email') ||
            data_get($attributes, 'send_telehealth_link_via_sms'))
        ) {
            // @todo change logic when "upheal" integration will be finished
            if (data_get($attributes, 'telehealth_provider') === Appointment::TELEHEALTH_PROVIDER_UPHEAL) {
                $this->sendUphealNotification($attributes, $patient, $provider, $appointment);
            } else {
                try {
                    $invitationDate = data_get($attributes, 'telehealth_notification_date') ? Carbon::parse($attributes['telehealth_notification_date']) : Carbon::now();
                    $googleMeeting = $this->createCalendarEventForAppointment($appointment);
                    $googleMeeting->allow_to_join_by_phone = $attributes['allow_to_join_by_phone'] ?? false;
                    $googleMeeting->save();
        
                    $this->sendNotification(
                        $googleMeeting,
                        $invitationDate,
                        $attributes['send_telehealth_link_via_email'] ?? null,
                        $attributes['send_telehealth_link_via_secondary_email'] ?? null,
                        $attributes['send_telehealth_link_via_sms'] ?? null,
                        $attributes['phone'] ?? null,
                        $attributes['email'] ?? null,
                        $attributes['secondary_email'] ?? null
                    );
                } catch (EmptyGoogleAccountException $exception) {
                    \App\Helpers\SentryLogger::captureException($exception);
                }
            }
        }

        if (Auth::check()) {
            $this->createAppointmentComment($appointment);
        }

        $this->createUphealPatient($provider, $patient);

        return $appointment->load(['patient', 'office', 'officeRoom']);
    }

    private function createUphealPatient(Provider $provider, Patient $patient)
    {
        if (isset($patient->upheal_user_id) || !$provider->works_with_upheal || empty($provider->upheal_user_id)) {
            return; 
        }

        $hasNewPatientDocument = PatientDocument::query()
            ->where('document_type_id', PatientDocumentType::getNewPatientId())
            ->where('patient_id', $patient->id)
            ->whereDate('created_at', '>=', PatientDocument::getUphealConsentAddedDate())
            ->exists();

        if (!$hasNewPatientDocument) {
            return;
        }

        try {
            UphealHelper::createPatient($provider, $patient);
        } catch (\Exception $exception) {
            \App\Helpers\SentryLogger::captureException($exception);
        }
    }

    private function createAppointmentComment(Appointment $appointment)
    {
        $data = [
            'patient_id' => $appointment->patients_id,
            'appointment_id' => $appointment->id,
            'comment_type' => PatientComment::CREATION_COMMENT_TYPE,
            'comment' => $appointment->notes,
            'metadata' => [
                'visit_reason' => $appointment->reason_for_visit ?? null,
            ]
        ];

        if (isset($data['comment'])) {
            $data['comment'] = preg_replace("/<div><br><\/div>/", " ", $data['comment']);
            $data['comment'] = preg_replace("/<div>/", "<br>", $data['comment']);
            $data['comment'] = strip_tags($data['comment'], '<span><br>');
            $data['comment'] = preg_replace("/<br>$/", "", $data['comment']);
            $data['comment'] = trim($data['comment']);
        }

        $user = \Auth::user();
        if ($user->isOnlyProvider()) {
            $data['provider_id'] = $user->provider_id;
        } else {
            $data['admin_id'] = $user->id;
        }

        $comment = PatientComment::create($data);
        if (isset($comment) && isset($data['comment'])) {
            \Bus::dispatchNow(new ParseCommentMentions($data['comment'], $comment->id, 'PatientComment', $comment->patient->id));
        }
    }

    /**
     * @param array $attributes
     * @param Appointment $appointment
     * @return Appointment
     */
    public function update(array $attributes, Appointment $appointment): Appointment
    {
        if (data_get($attributes, 'phone') && data_get($attributes, 'send_telehealth_link_via_sms')) {
            try {
                TwilioLookup::validatePhone(data_get($attributes, 'phone'));
            } catch (RestException | PhoneIsUnableToReceiveSmsException $e) {
                // exception is handled in App\Exceptions\Handler
                throw new PhoneIsUnableToReceiveSmsException(data_get($attributes, 'phone'));
            }
        }

        $oldTime = $appointment->time;
        $status = $appointment->status;
        if(auth()->user()->isAdmin()) {
//            $status = Status::whereId($attributes['status'])->first();
            $provider = Provider::withTrashed()->whereId($attributes['provider_id'])->first();
        } else {
//            $status = $appointment->status;
            $provider = $appointment->provider()->withTrashed()->first();
        }

        if (!isset($attributes['patient_id'])) {
            $attributes['patient_id'] = $appointment->patient->id;
        }
        
        $patient = Patient::whereId($attributes['patient_id'])->first();
        $office = Office::whereId($attributes['office_id'])->first();

        if (!isset($attributes['date'])) {
            $attributes['date'] = Carbon::createFromTimestamp($appointment->time)->format('Y-m-d');
        }

        if($attributes['visit_type'] === VisitType::VIRTUAL) {
            $room = null;
        } else if(isset($attributes['office_room'])) {
            $room = OfficeRoom::query()->where('id', $attributes['office_room'])->first();
        } else {
            $room = $appointment->officeRoom;
        }

        $treatmentModality = TreatmentModality::find(data_get($attributes, 'reason_for_visit'));
        $isInitial = in_array($treatmentModality->id, TreatmentModality::initialEvaluationIds());
        $reasonForVisit = $treatmentModality->name;
        $visitLength = $attributes['visit_length'] ?? $treatmentModality->duration;

        $data = [
            'id' => isset($appointment->idAppointments) ? (int)$appointment->idAppointments : null,
            'date' => Carbon::parse($attributes['date'] . ' ' . $attributes['time']),
            'officeId' => (int)$office->external_id,
            'patientId' => (int)$patient->patient_id,
            'reasonForVisit' => $reasonForVisit,
            'providerId' => (int)$provider->officeally_id,
            'visitLength' => intval($visitLength),
            'statusId' => (int)$status->external_id,
            'notes' => $attributes['notes'] ?? null,
            'resource' => new AppointmentResource([
                'type' => optional($room)->external_id ? AppointmentResource::TYPE_ROOM : 0,
                'id' => optional($room)->external_id ? (int)$room->external_id : 0,
            ]),
        ];

        RetryJobQueueHelper::dispatchRetryEditAppointment(Option::OA_ACCOUNT_1, $data, $appointment->id);
        
        $isFirstPatientAppointmentWithProvider = false;

        $updatedData = [
            'time' => strtotime($attributes['date'] . ' ' . $attributes['time']),
            'visit_length' => $visitLength,
            'notes' => $attributes['notes'] ?? null,
            'custom_notes' => $attributes['comment'] ?? null,
            'reason_for_visit' => $reasonForVisit,
            'is_initial' => $isInitial,
            'treatment_modality_id' => $attributes['reason_for_visit'],
            'sheldued_by' => $userName = auth()->user()->provider ? auth()->user()->provider->provider_name : auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname,
            'not_found_count' => 0,
            'providers_id' => $provider->id,
            'patients_id' => $attributes['patient_id'],
            'offices_id' => $attributes['office_id'],
            'office_room_id' => optional($room)->id,
            'appointment_statuses_id' => $status->id,
        ];

        if (isset($attributes['reschedule_sub_status_id'])) {
            $updatedData['reschedule_sub_status_id'] = $attributes['reschedule_sub_status_id'];
        }

        $appointment->update($updatedData);

        $appointmentTime = Carbon::createFromTimestamp($appointment->time);

        if ($this->connectProvider($provider, $patient)) {
            $isFirstPatientAppointmentWithProvider = true;
        }

        if ($isFirstPatientAppointmentWithProvider && $appointmentTime->isFuture()) {
            $this->sendFirstAppointmentNotifications($appointment, $provider, $patient, $office);
        }

        $this->setPatientEmailIfEmpty($patient, $attributes['email'] ?? null);

        $this->handleVirtualAppointment($appointment, $attributes, $oldTime);

        return $appointment->load(['patient', 'office', 'officeRoom']);
    }

    protected function sendUphealNotification(array $attributes, Patient $patient, Provider $provider, Appointment $appointment, UphealMeeting $uphealMeeting = null)
    {
        if (
            !data_get($attributes, 'send_telehealth_link_via_email') && 
            !data_get($attributes, 'send_telehealth_link_via_secondary_email') && 
            !data_get($attributes, 'send_telehealth_link_via_sms')
        ) {
            return;
        }

        try {
            if (empty($patient->upheal_user_id)) {
                UphealHelper::createPatient($provider, $patient);
            }

            if (empty($uphealMeeting)) {
                $uphealMeeting = UphealMeeting::create([
                    'patient_id' => $patient->id,
                    'provider_id' => $provider->id,
                    'appointment_id' => $appointment->id,
                ]);
            }
            
            $invitationDate = data_get($attributes, 'telehealth_notification_date')
                ? Carbon::parse($attributes['telehealth_notification_date'])
                : Carbon::now();

            UphealHelper::sendNotification(
                $uphealMeeting,
                $invitationDate,
                $attributes['send_telehealth_link_via_email'] ?? null,
                $attributes['send_telehealth_link_via_secondary_email'] ?? null,
                $attributes['send_telehealth_link_via_sms'] ?? null,
                $attributes['phone'] ?? null,
                $attributes['email'] ?? null,
                $attributes['secondary_email'] ?? null
            );
        } catch (\Exception $exception) {
            \App\Helpers\SentryLogger::captureException($exception);
        }
    }

    /**
     * @param Appointment $appointment
     * @param array $attributes
     * @param $oldTime
     * @return GoogleMeeting
     */
    protected function updateGoogleMeetAndSendNotification(Appointment $appointment, array $attributes, $oldTime)
    {
        $googleMeet = GoogleMeeting::whereAppointmentId($appointment->id)->first();
        if (empty($googleMeet)) {
            if(
                data_get($attributes, 'send_telehealth_link_via_email') || 
                data_get($attributes, 'send_telehealth_link_via_secondary_email') || 
                data_get($attributes, 'send_telehealth_link_via_sms')
                ) {
                try {
                    $googleMeet = $this->createCalendarEventForAppointment($appointment);
                } catch (EmptyGoogleAccountException $exception) {
                    \App\Helpers\SentryLogger::captureException($exception);
                }
            }
        } else if ($oldTime !== strtotime($attributes['date'] . ' ' . $attributes['time'])) {
            $this->updateCalendarEvent($googleMeet);
        }

        if($googleMeet) {
            $googleMeet->allow_to_join_by_phone = $attributes['allow_to_join_by_phone'] ?? false;
            $googleMeet->save();
            $invitationDate = data_get($attributes, 'telehealth_notification_date') ? Carbon::parse($attributes['telehealth_notification_date']) : Carbon::now();
            $this->sendNotification(
                $googleMeet,
                $invitationDate,
                $attributes['send_telehealth_link_via_email'] ?? null,
                $attributes['send_telehealth_link_via_secondary_email'] ?? null,
                $attributes['send_telehealth_link_via_sms'] ?? null,
                $attributes['phone'] ?? null,
                $attributes['email'] ?? null,
                $attributes['secondary_email'] ?? null
            );
        }

        return $googleMeet;
    }

    public function rescheduleAppointment(array $attributes, Appointment $appointment): Appointment
    {
        $oldTime = $appointment->time;
        $newTime = strtotime($attributes['date'] . ' ' . $attributes['time']);
        $oldTimeCarbon = Carbon::createFromTimestamp($oldTime);
        $newTimeCarbon = Carbon::createFromTimestamp($newTime);

        if ($oldTimeCarbon->isSameDay($newTimeCarbon)) {
            return $this->update($attributes, $appointment);
        }

        if (data_get($attributes, 'phone') && data_get($attributes, 'send_telehealth_link_via_sms')) {
            try {
                TwilioLookup::validatePhone(data_get($attributes, 'phone'));
            } catch (RestException | PhoneIsUnableToReceiveSmsException $e) {
                // exception is handled in App\Exceptions\Handler
                throw new PhoneIsUnableToReceiveSmsException(data_get($attributes, 'phone'));
            }
        }

        $provider = Provider::withTrashed()->whereId($appointment->providers_id)->first();
        $patient = Patient::whereId($appointment->patients_id)->first();
        $office = Office::whereId($attributes['office_id'])->first();

        if(isset($attributes['office_room'])) {
            $room = OfficeRoom::whereId($attributes['office_room'])->first();
        } else {
            $room = null;
        }

        $rescheduledId = Status::getRescheduledId();
        $statusName = Status::find($rescheduledId)->external_id;

        $treatmentModality = TreatmentModality::find(data_get($attributes, 'reason_for_visit'));
        $isInitial = in_array($treatmentModality->id, TreatmentModality::initialEvaluationIds());
        $reasonForVisit = $treatmentModality->name;
        $visitLength = $attributes['visit_length'] ?? $treatmentModality->duration;

        $appointmentId = null;

        $newAppointment = $appointment->replicate();
        $newAppointment->rescheduled_appointment_id = $attributes['id'];
        $newAppointment->idAppointments = $appointmentId;
        $newAppointment->time = $newTime;
        $newAppointment->offices_id = $attributes['office_id'];
        $newAppointment->office_room_id = $attributes['office_room'];
        $newAppointment->reason_for_visit = $reasonForVisit;
        $newAppointment->is_initial = $isInitial;
        $newAppointment->treatment_modality_id = $attributes['reason_for_visit'];
        $newAppointment->visit_length = $visitLength;
        $newAppointment->custom_notes = null;
        $newAppointment->reschedule_sub_status_id = null;
        $newAppointment->save();

        $account = Option::OA_ACCOUNT_2;
        $officeAlly = new \App\Helpers\Sites\OfficeAlly\OfficeAllyHelper($account);
        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        $appointment->update([
            'appointment_statuses_id' => $rescheduledId,
            'new_status_id' => $rescheduledId,
            'custom_notes' => $attributes['comment'],
            'start_completing_date' => Carbon::now(),
            'reschedule_sub_status_id' => $attributes['reschedule_sub_status_id'],
        ]);

        $dataForUpdate = [
            'id' => isset($appointment->idAppointments) ? (int)$appointment->idAppointments : null,
            'date' => Carbon::createFromTimestamp($appointment->time),
            'officeId' => (int)$appointment->office->external_id,
            'patientId' => (int)$appointment->patient->patient_id,
            'reasonForVisit' => $appointment->reason_for_visit,
            'providerId' => (int)$appointment->provider->officeally_id,
            'visitLength' => $visitLength,
            'resource' => new AppointmentResource([
                'type' => optional($appointment->officeRoom)->external_id  ? AppointmentResource::TYPE_ROOM : 0,
                'id' => optional($appointment->officeRoom)->external_id  ? (int)$appointment->officeRoom->external_id : 0,
            ]),
            'statusId' => (int)$statusName,
            'notes' => !empty($comment) ? $comment : $appointment->notes,
        ];

        RetryJobQueueHelper::dispatchRetryEditAppointment($account, $dataForUpdate, $appointment->id);

        $dataForCreate = new \App\DTO\OfficeAlly\Appointment([
            'date' => Carbon::parse($attributes['date'] . ' ' . $attributes['time']),
            'officeId' => (int)$office->external_id,
            'patientId' => (int)$patient->patient_id,
            'reasonForVisit' => $reasonForVisit,
            'providerId' =>  (int)$provider->officeally_id,
            'visitLength' => intval($visitLength),
            'resource' => new AppointmentResource([
                'type' => optional($room)->external_id ? AppointmentResource::TYPE_ROOM : 0,
                'id' => optional($room)->external_id ? (int)$room->external_id : 0,
            ]),
            'statusId' => AppointmentStatuses::ACTIVE,
            'notes' => $attributes['notes'] ?? null,
        ]);

        if ($patient->patient_id) {
            try {
                $appointmentId = $officeAlly->createAppointment($dataForCreate);
            } catch (OfficeallyAuthenticationException $e) {
                $job = (new RetryCreateAppointment($account, $dataForCreate, $newAppointment->id, $patient->id))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryCreateAppointment($account, $dataForCreate, $newAppointment->id, $patient->id))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }

        if ($appointmentId) {
            $newAppointment->update(['idAppointments' => (int)$appointmentId]);
        }

        $this->handleVirtualAppointment($appointment, $attributes, $oldTime);

        return $newAppointment;
    }

    /**
     * @param Appointment $appointment
     * @return bool
     * @throws \Exception
     */
    public function delete(\App\Appointment $appointment): bool
    {
        $account = Option::OA_ACCOUNT_1;
        $officeAlly = new OfficeAllyHelper($account);

        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        if ($appointment->idAppointments) {
            try {
                $officeAlly->deleteAppointment($appointment->idAppointments);
            } catch (OfficeallyAuthenticationException $e) {
                $job = (new RetryDeleteAppointment($account, $appointment->id))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryDeleteAppointment($account, $appointment->id))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }

        try {
            if (isset($appointment->googleMeet)) {
                $appointment->googleMeet->invitations()->delete();
                $appointment->googleMeet->delete();
            }

            if (isset($appointment->uphealMeet)) {
                $appointment->uphealMeet->invitations()->delete();
                $appointment->uphealMeet->delete();
            }
            $appointment->delete();
            $time = Carbon::createFromTimestamp($appointment->time);
            $userName = auth()->user()->provider ? auth()->user()->provider->provider_name : auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
            event(
                new NeedsWriteSystemComment($appointment->patients_id,
                    trans('comments.admin_was_delete_appointment', [
                        'user_name' => $userName,
                        'apptdate' => $time->format('m/d/Y'),
                        'appttime' => $time->format('h:iA'),
                    ]))
            );

            return true;
        } catch (\Exception $exception) {
            \App\Helpers\SentryLogger::captureException($exception);
            return false;
        }
    }
    
    public function getAvailableStatuses(): array
    {
        return array_prepend(Status::getOtherCancelStatuses(), Status::select(['id', 'status'])->where('status', 'Active')->first()->toArray());
    }
    
    public function visitCorrectionDataByAppointment(Appointment $appointment, bool $allowChangeCpt = true, bool $allowChangePos = true, bool $allowChangeModifierA = true)
    {
        $appointment->load(['patient.templates']);
        
        return $this->visitCorrectionData(
            $appointment,
            (string)optional($appointment->patient->insurance)->insurance,
            (string)data_get($appointment, 'patient.templates.0.pos'),
            (string)data_get($appointment, 'patient.templates.0.cpt'),
            (string)data_get($appointment, 'patient.templates.0.modifier_a'),
            $allowChangeCpt,
            $allowChangePos,
            $allowChangeModifierA
        );
    }
    
    public function visitCorrectionData(Appointment $appointment, string $insuranceName, string $oldPos, string $oldCpt, string $oldModifierA, bool $allowChangeCpt = true, bool $allowChangePos = true, bool $allowChangeModifierA = true)
    {
        $data = [
            'change_modifier_a' => null,
            'change_pos' => null,
            'change_cpt' => null,
        ];
        $isTelehealth = (bool)optional($appointment->treatmentModality)->is_telehealth;
        $newPos = $this->getPOS($isTelehealth, $insuranceName);
        $newModifierA = $this->getModifierA($isTelehealth, $insuranceName);

        if ($allowChangePos && $oldPos != $newPos) {
            $data['change_pos']['from'] = $oldPos;
            $data['change_pos']['to'] = $newPos;
        }

        if ($allowChangeModifierA && $oldModifierA != $newModifierA) {
            $data['change_modifier_a']['from'] = $oldModifierA;
            $data['change_modifier_a']['to'] = $newModifierA;
        }
    
        if ($allowChangeCpt) {
            $newCpt = optional($appointment->treatmentModality)->insuranceProcedure;

            if (isset($newCpt) && $oldCpt != $newCpt->code) {
                $data['change_cpt']['from'] = $oldCpt;
                $data['change_cpt']['to'] = $newCpt->code;
                $data['change_cpt']['charge'] = $newCpt->charge;
            }
        }
        
        return $data;
    }

    private function getPOS(bool $isTelehealth, string $insuranceName)
    {
        if (!$isTelehealth) {
            return '11';
        }
        if (str_contains(strtolower($insuranceName), 'beacon') || str_contains(strtolower($insuranceName), 'medicare')) {
            return '11';
        }

        return '02';
    }

    private function getModifierA(bool $isTelehealth, string $insuranceName)
    {
        if (!$isTelehealth) {
            return '';
        }
        if (str_contains(strtolower($insuranceName), 'beacon') || str_contains(strtolower($insuranceName), 'medicare')) {
            return 'GT';
        }
        
        return '95';
    }
    
    public function importantDirties(array $ids)
    {
        $appointments = [];

        Appointment::query()
            ->whereKey($ids)
            ->with([
                'provider' => function($query) {
                    $query->withTrashed();
                },
                'patient.insurance', 'patient.templates'
            ])
            ->orderByDesc('time')
            ->orderByDesc('id')
            ->each(function(Appointment $appointment) use (&$appointments) {
                $providerSupervisor = ProviderSupervisor::getSupervisorForDate($appointment->providers_id, Carbon::createFromTimestamp($appointment->time));
                $oldCpt = (string)data_get($appointment, 'patient.templates.0.cpt');
                $noChangeCpts = [PatientInsuranceProcedure::EAP_CPT_CODE, PatientInsuranceProcedure::CASH_CPT_CODE];

                $data = [
                    'id' => $appointment->getKey(),
                    'date_of_service' => Carbon::createFromTimestamp($appointment->time)->toDateTimeString(),
                    'patient_name' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                    'patient_id' => $appointment->patient->getKey(),
                    'external_patient_id' => $appointment->patient->patient_id,
                    'therapist_name' => optional($appointment->provider)->provider_name,
                    'insurance' => optional($appointment->patient->insurance)->insurance,
                    'reason_for_visit' => $appointment->reason_for_visit,
                    'accept_change_pos' => true,
                    'accept_change_modifier_a' => true,
                    'accept_change_cpt' => !in_array($oldCpt, $noChangeCpts),
                    'change_pos' => null,
                    'change_modifier_a' => null,
                    'change_cpt' => null,
                    'supervisor' => isset($providerSupervisor) ? $providerSupervisor->supervisor()->withTrashed()->first() : null
                ];
                $data = array_merge($data, $this->visitCorrectionDataByAppointment($appointment));
                
                $appointments[] = $data;
            });
        
        return $appointments;
    }

    public function createVisitForAppointment(Appointment $appointment)
    {
        if (config('app.visits_with_completed_appointments_to_salary') != true) {
            return;
        }

        $providerTariffPlan = DB::table('providers_tariffs_plans')
            ->where('provider_id', $appointment->providers_id)
            ->first();

        $treatmentModality = $appointment->treatmentModality()->first();

        PatientVisit::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patients_id,
            'provider_id' => $appointment->providers_id,
            'provider_tariff_plan_id' => optional($providerTariffPlan)->tariff_plan_id,
            'insurance_id' => $appointment->patient->primary_insurance_id,
            'plan_id' => $appointment->patient->insurance_plan_id,
            'procedure_id' => optional($treatmentModality)->insurance_procedure_id,
            'is_telehealth' => optional($treatmentModality)->is_telehealth,
            'date' => Carbon::createFromTimestamp($appointment->time)->toDateString(),
            'needs_update_salary' => true,
            'is_update_salary_enabled' => true,
            'from_completed_appointment' => true,
        ]);

        dispatch(new SyncSalaryData());
    }

    public function createLateCancellationTransaction(Appointment $appointment, $chargeForCancellation)
    {
        $billingPeriodId = optional($appointment->provider)->billing_period_type_id;
        if (empty($billingPeriodId)) {
            return;
        }

        $billingPeriod = BillingPeriod::getBillingPeriodByDate(Carbon::createFromTimestamp($appointment->time), $billingPeriodId);
        if (empty($billingPeriod)) {
            return;
        }

        SalaryTimesheetLateCancellation::updateOrCreate([
            'appointment_id' => $appointment->getKey(),
        ], [
            'billing_period_id' => $billingPeriod->getKey(),
            'patient_id' => $appointment->patients_id,
            'provider_id' => $appointment->provider->getKey(),
            'date' => Carbon::today()->toDateString(),
            'amount' => $chargeForCancellation,
            'is_custom_created' => false,
        ]);

        SalaryTimesheet::firstOrCreate([
            'provider_id' => $appointment->provider->getKey(),
            'billing_period_id' => $billingPeriod->getKey(),
        ], [
            'seek_time' => 0,
            'monthly_meeting_attended' => false,
            'changed_appointment_statuses' => false,
            'completed_ia_and_pn' => false,
            'set_diagnoses' => false,
            'completed_timesheet' => false,
        ]);

        LateCancellationTransaction::updateOrCreate([
            'appointment_id' => $appointment->getKey(),
        ], [
            'payment_amount' => $chargeForCancellation * 100,
            'user_id' => auth()->id(),
            'transaction_date' => Carbon::now()->toDateTimeString(),
        ]);

        \Bus::dispatchNow(new CalculatePatientBalance([$appointment->patients_id]));
    }
    
    private function handleVirtualAppointment($appointment, $attributes, $oldTime)
    {
        if ($attributes['visit_type'] !== VisitType::VIRTUAL) {
            $appointment->removeMeetings();
            return;
        }

        $invitations = null;

        if (isset($appointment->googleMeet)) {
            $invitations = $appointment->googleMeet->invitations;
        }

        if (isset($appointment->uphealMeet)) {
            $invitations = $appointment->uphealMeet->invitations;
        }

        $rescheduledAppointment = Appointment::where('rescheduled_appointment_id', $appointment->id)->first();
        if (isset($rescheduledAppointment)) {
            $appointment->googleMeet()->update(['appointment_id' => $rescheduledAppointment->id]);
            $appointment->uphealMeet()->update(['appointment_id' => $rescheduledAppointment->id]);
            $appointment = $rescheduledAppointment;
        }

        if (
            !data_get($attributes, 'send_telehealth_link_via_email') &&
            !data_get($attributes, 'send_telehealth_link_via_secondary_email') &&
            !data_get($attributes, 'send_telehealth_link_via_sms')  && 
            $appointment->time !== $oldTime &&
            isset($invitations)
        ) {
            $newTelehealthNotificationData = Carbon::createFromTimestamp($appointment->time)->subHour()->toDateTimeString();
            $invitations->each(function ($invitation) use ($newTelehealthNotificationData) {
                $invitation->update(['send_at' => $newTelehealthNotificationData]);
            });
            return;
        }

        // @todo change logic when "upheal" integration will be finished
        if (data_get($attributes, 'telehealth_provider') === Appointment::TELEHEALTH_PROVIDER_UPHEAL) {
            $appointment->googleMeet()->each(function ($meeting) {
                $meeting->invitations()->delete();
                $meeting->delete();
            });
            $uphealMeeting = UphealMeeting::where('appointment_id', $appointment->id)->first();
            $this->sendUphealNotification($attributes, $appointment->patient, $appointment->provider, $appointment, $uphealMeeting);
        } else {
            $appointment->uphealMeet()->each(function ($meeting) {
                $meeting->invitations()->delete();
                $meeting->delete();
            });
            $this->updateGoogleMeetAndSendNotification($appointment, $attributes, $oldTime);
        }
    }
}
