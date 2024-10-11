<?php

namespace App\Providers;

use App\Appointment;
use App\Availability;
use App\Enums\PatientPreferredPhone;
use App\Enums\Sex;
use App\Enums\State;
use App\Enums\TimeEnum;
use App\Enums\VisitType;
use App\Models\Patient\Inquiry\PatientInquiryStage;
use App\Models\Patient\Lead\PatientLead;
use App\Models\TreatmentModality;
use App\OfficeRoom;
use App\Patient;
use App\PatientInsurance;
use App\PatientVisitFrequency;
use App\Status;
use App\User;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Void_;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->dateStartTodayValidation();
        $this->maxAppointments();
        $this->visitLengthValidation();
        $this->visitTypeValidation();
        $this->dayTimeValidation();
        $this->roomValidation();
        $this->patientAppointmentValidation();
        $this->providerAppointmentValidation();
        $this->providerAvailabilityValidation();
        $this->appointmentRepeatValidation();
        $this->sexValidation();
        $this->stateValidation();
        $this->patientPreferredPhoneValidation();
        $this->patientSubscriberValidation();
        $this->phoneValidation();
        $this->existsInPatientsOrPatientLeadsValidation();
        $this->patientInquiryStageIsNotLowerThanCurrent();
        $this->uniqueFirstnameLastnameDateOfBirth();
        $this->changeStageRequirementsAreMet();
        $this->patientInsuranceExpires();
        $this->appointmentReschedulingAccordingToPatientVisitFrequency();
        $this->endTimeTreatmentModality();
        $this->cardBelongsToPatient();
        $this->patientRequestedCancellationAt();
        $this->lte();
        $this->formHasBeenSent();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * @return void
     */
    public function dateStartTodayValidation()
    {
        Validator::extend('date_start_today', function ($attribute, $value, $parameters, $validator) {
            return Carbon::parse($value) >= Carbon::now()->startOfDay();
        });
    }
    
    public function maxAppointments()
    {
        Validator::extend('max_appointments_per_day', function ($attribute, $value, $parameters, $validator) {
            $providerId = $validator->attributes()['provider_id'];
            $maxAppointments = $parameters[0];
            $date = Carbon::parse($value);
            
            return Appointment::query()
                ->where('providers_id', $providerId)
                ->whereBetween('time', [$date->copy()->startOfDay()->timestamp, $date->copy()->endOfDay()->timestamp])
                ->whereIn('appointment_statuses_id', [
                    Status::getActiveId(),
                    Status::getCompletedId(),
                    Status::getVisitCreatedId(),
                ])
                ->when($this->app->request->route('appointment'), function($query, $appointment) {
                    $query->where('appointments.id', '!=', $appointment->getKey());
                })
                ->count() < $maxAppointments;
        });
    
        Validator::replacer('max_appointments_per_day', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':max', $parameters[0], $message);
        });
    }

    /**
     * @return void
     */
    public function visitLengthValidation()
    {
        Validator::extend('visit_length', function ($attribute, $value, $parameters, $validator) {
            return in_array(intval($value), TimeEnum::VISIT_LENGTHS);
        });
    }

    /**
     * @return void
     */
    public function visitTypeValidation()
    {
        Validator::extend('visit_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, VisitType::$list);
        });
    }

    /**
     * @return void
     */
    public function dayTimeValidation()
    {
        Validator::extend('day_time', function ($attribute, $value, $parameters, $validator) {
            return Carbon::parse($value) >= Carbon::parse(TimeEnum::START_DAY_RANGE)
                && Carbon::parse($value) <= Carbon::parse(TimeEnum::END_DAY_RANGE);
        });
    }

    /**
     * @return void
     */
    public function roomValidation()
    {
        Validator::extend('office_room', function ($attribute, $value, $parameters, $validator) {

            $room = OfficeRoom::whereId($value)->first();

            if (empty($room) && $validator->attributes()['visit_type'] !== VisitType::IN_PERSON) {
                return true;
            }

            if (empty($room) || intval($room->office_id) !== intval($validator->attributes()['office_id'] ?? null)
                || empty($validator->attributes()['date']) || empty($validator->attributes()['time'])
                || empty($validator->attributes()['reason_for_visit'])) {
                return false;
            }

            $minutes = (int)TreatmentModality::find($validator->attributes()['reason_for_visit'])->duration;
            $dateStart = Carbon::parse($validator->attributes()['date'] . $validator->attributes()['time'])->timestamp;
            $dateEnd = Carbon::parse($validator->attributes()['date'] . $validator->attributes()['time'])->addMinutes($minutes)->timestamp;

            $appointments = Appointment::where('office_room_id', $room->id)
                ->statusNotCancel()
                ->where('offices_id', $room->office_id)
                ->where(function ($query) use ($dateStart, $dateEnd) {
                    $query->whereRaw('time BETWEEN ' . $dateStart . ' AND ' . ($dateEnd - 1));
                    $query->orWhereRaw('(time + (visit_length * 60)) BETWEEN ' . ($dateStart + 1) . ' AND ' . $dateEnd);
                });

            if (!empty($this->app->request->route('appointment'))) {
                $appointments->whereNotIn('id', [$this->app->request->route('appointment')->id]);
            }

            return $appointments->count() === 0;
        });
    }

    /**
     * @return void
     */
    public function patientAppointmentValidation()
    {
        Validator::extend('patient_appointment', function ($attribute, $value, $parameters, $validator) {
            $dateStart = Carbon::parse($validator->attributes()['date'])->startOfDay()->timestamp + 1;
            $dateEnd = Carbon::parse($validator->attributes()['date'])->endOfDay()->timestamp - 1;

            $appointments = Appointment::where('patients_id', $value)
                ->whereRaw('time BETWEEN ' . $dateStart . ' AND ' . $dateEnd)
                ->whereIn('appointment_statuses_id', Status::getActiveCompletedVisitCreatedStatusesId());

            if (!empty($this->app->request->route('appointment'))) {
                $appointments->whereNotIn('id', [$this->app->request->route('appointment')->id]);
            }

            return $appointments->count() === 0;
        });
    }

    public function appointmentRepeatValidation()
    {
        Validator::extend('appointment_repeat', function ($attribute, $value, $parameters, $validator) {
            if (empty($value)) {
                return true;
            }

            $patient = Patient::find($validator->attributes()['patient_id']);
            $daysBetweenVisits = $patient->getDaysBetweenVisits();
            if (!$daysBetweenVisits) {
                $validator->addReplacer(
                    'appointment_repeat',
                    function ($message, $attribute, $rule, $parameters) {
                        return 'Repeat functionality is not available for this patient.';
                    }
                );
                return false;
            }

            $prevAppointmentDate = Carbon::parse($validator->attributes()['date'] . ' ' . $validator->attributes()['time']);
            $minutes = (int)TreatmentModality::find($validator->attributes()['reason_for_visit'])->duration;

            for ($i = 1; $i <= $value; $i++) {
                $date = $prevAppointmentDate->addDays($daysBetweenVisits);

                $dateStart = $date->copy()->startOfDay()->timestamp + 1;
                $dateEnd = $date->copy()->endOfDay()->timestamp - 1;

                $patientApptExists = Appointment::query()
                    ->where('patients_id', $validator->attributes()['patient_id'])
                    ->whereRaw('time BETWEEN ' . $dateStart . ' AND ' . $dateEnd)
                    ->whereIn('appointment_statuses_id', Status::getActiveCompletedVisitCreatedStatusesId())
                    ->exists();

                if ($patientApptExists) {
                    return false;
                }

                $dateStart = $date->timestamp;
                $dateEnd = $date->copy()->addMinutes($minutes)->timestamp;
    
                $providerApptExists = Appointment::query()
                    ->where('providers_id', $validator->attributes()['provider_id'])
                    ->where(function ($query) use ($dateStart, $dateEnd) {
                        $query->whereRaw('time BETWEEN ' . $dateStart . ' AND ' . ($dateEnd - 1));
                        $query->orWhereRaw('(time + (visit_length * 60)) BETWEEN ' . ($dateStart + 1) . ' AND ' . $dateEnd);
                    })
                    ->whereIn('appointment_statuses_id', Status::getActiveCompletedVisitCreatedStatusesId())
                    ->exists();

                if ($providerApptExists) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * @return void
     */
    public function providerAppointmentValidation()
    {
        Validator::extend('provider_appointment', function ($attribute, $value, $parameters, $validator) {
            $minutes = (int)TreatmentModality::find($validator->attributes()['reason_for_visit'])->duration;
            $dateStart = Carbon::parse($validator->attributes()['date'] . $validator->attributes()['time'])->timestamp;
            $dateEnd = Carbon::parse($validator->attributes()['date'] . $validator->attributes()['time'])->addMinutes($minutes)->timestamp;

            $appointments = Appointment::where('providers_id', $value)
                ->where(function ($query) use ($dateStart, $dateEnd) {
                    $query->whereRaw('time BETWEEN ' . $dateStart . ' AND ' . ($dateEnd - 1));
                    $query->orWhereRaw('(time + (visit_length * 60)) BETWEEN ' . ($dateStart + 1) . ' AND ' . $dateEnd);
                })
                ->whereIn('appointment_statuses_id', Status::getActiveCompletedVisitCreatedStatusesId());

            if (!empty($this->app->request->route('appointment'))) {
                $appointments->whereNotIn('id', [$this->app->request->route('appointment')->id]);
            }

            return $appointments->count() === 0;
        });
    }

    public function providerAvailabilityValidation()
    {
        Validator::extend('provider_availability', function ($attribute, $value, $parameters, $validator) {
            $minutes = intval($validator->attributes()['length']);
            $dateStart = Carbon::parse($value);
            $dateEnd = Carbon::parse($value)->addMinutes($minutes);

            $availabilities = Availability::query()
                ->where('provider_id', Auth::user()->provider_id)
                ->whereDate('start_date', '=', $dateStart->toDateString())
                ->whereRaw("(
                    (`start_time` >= '{$dateStart->format('H:i:s')}' AND `start_time` < '{$dateEnd->format('H:i:s')}')
                    OR ((`start_time` + INTERVAL `length` MINUTE) > '{$dateStart->format('H:i:s')}' AND (`start_time` + INTERVAL `length` MINUTE) <= '{$dateEnd->format('H:i:s')}')
                )");

            if (isset($validator->attributes()['id'])) {
                $availabilities->where('id', '!=', $validator->attributes()['id']);
            }

            return $availabilities->count() === 0;
        });
    }

    /**
     * @return void
     */
    public function sexValidation()
    {
        Validator::extend('sex', function ($attribute, $value, $parameters, $validator) {
            return !empty(Sex::$list[$value]);
        });
    }

    /**
     * @return void
     */
    public function stateValidation()
    {
        Validator::extend('state', function ($attribute, $value, $parameters, $validator) {
            return array_search($value, State::$list) !== false;
        });
    }

    /**
     * @return void
     */
    public function patientPreferredPhoneValidation()
    {
        Validator::extend('patient_preferred_phone', function ($attribute, $value, $parameters, $validator) {
            return array_search($value, PatientPreferredPhone::$list) !== false;
        });
    }

    /**
     * @return void
     */
    public function patientSubscriberValidation()
    {
        Validator::extend('patient_subscriber', function ($attribute, $value, $parameters, $validator) {
            $insurance = PatientInsurance::whereId($validator->attributes()['insurance_id'] ?? null)->first();

            return !empty($insurance) && Str::contains($insurance->insurance, ['Kaiser'])
                ? ctype_digit($value)
                : true;
        });
    }

    /**
     * @return void
     */
    public function phoneValidation()
    {
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return !!preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}\Z/' ,$value);
        });
    }

    /**
     * @return void
     */
    public function existsInPatientsOrPatientLeadsValidation()
    {
        Validator::extend('exists_in_patients_or_patient_leads', function ($attribute, $value, $parameters, $validator) {
            if (! $value) {
                return true;
            }

            $model_classname = request()->input('inquirable_classname');

            if ($model_classname === class_basename(Patient::class)) {
                return Patient::where('id', $value)->exists();
            }

            if ($model_classname === class_basename(PatientLead::class)) {
                return PatientLead::where('id', $value)->exists();
            }
        }, 'Given id not exist in neither patients nor patient_leads');
    }

    /**
     * @return void
     */
    public function patientInquiryStageIsNotLowerThanCurrent()
    {
        Validator::extend('patient_inquiry_stage_is_not_lower_than_current', function ($attribute, $value, $parameters, $validator) {
            if (! $value) {
                return true;
            }

            $inquiry = request()->inquiry;

            if (! $inquiry) {
                return false;
            }

            return $value > $inquiry->stage_id;
        }, 'stage_id for inquiry must be bigger than current');
    }

    public function uniqueFirstnameLastnameDateOfBirth()
    {
        Validator::extend('unique_firstname_lastname_date_of_birth', function ($attribute, $value, $parameters, $validator) {
            $firstName = request()->first_name;
            $lastName = request()->last_name;
            $dateOfBirth = request()->date_of_birth;

            if ($value || ! $firstName || ! $lastName || ! $dateOfBirth) {
                return true;
            }

            $dateOfBirth = Carbon::parse($dateOfBirth)->toDateString();

            $isExistingPatient = Patient::query()
                ->where([
                    'first_name'    => $firstName,
                    'last_name'     => $lastName,
                    'date_of_birth' => $dateOfBirth,
                ])
                ->exists();

            if ($isExistingPatient) {
                return false;
            }

            $isExistingPatientLead = PatientLead::query()
                ->where([
                    'first_name'    => $firstName,
                    'last_name'     => $lastName,
                    'date_of_birth' => $dateOfBirth,
                ])
                ->exists();

            return ! $isExistingPatientLead;
        }, 'Patient with following data is already exist. You cannot create patient with selected firstname, lastname and date of birth. Please, connect inquiry to existing patient or input other data');
    }

    public function changeStageRequirementsAreMet()
    {
        Validator::extend('change_stage_requirements_are_met', function ($attribute, $value, $parameters, $validator) {
            $user = User::with('roles')->find(Auth::user()->id);

            $inquiry = request()->inquiry;
            $stageId = request()->stage_id;

            if (!$inquiry || !$stageId) {
                $validator->addReplacer('change_stage_requirements_are_met',
                    function($message, $attribute, $rule, $parameters) {
                        return \str_replace(':reason','Request does not have stage_id or inquiry', $message);
                    }
                );

                return false;
            }

            if ($inquiry->stage_id === $stageId) {
                $validator->addReplacer('change_stage_requirements_are_met',
                    function($message, $attribute, $rule, $parameters) {
                        return \str_replace(':reason','Inquiry is already in this stage. Please, reload the page', $message);
                    }
                );

                return false;
            }

            if ($user->isPatientRelationManager()) {
                $validator->addReplacer(
                    'change_stage_requirements_are_met',
                    function ($message) {
                        return \str_replace(':reason', 'No access', $message);
                    }
                );

                if ($stageId !== PatientInquiryStage::getOnboardingCompleteId() && $stageId !== PatientInquiryStage::getInitialAppointmentCompleteId()) {
                    return false;
                }
            }

            if (
                $stageId === PatientInquiryStage::getAppointmentScheduledId() || $stageId === PatientInquiryStage::getOnHoldId() ||
                $stageId === PatientInquiryStage::getOnboardingCompleteId() || $stageId === PatientInquiryStage::getInitialAppointmentCompleteId()
            ) {
                if (!$inquiry->isPatientCreated()) {
                    $validator->addReplacer(
                        'change_stage_requirements_are_met',
                        function ($message, $attribute, $rule, $parameters) {
                            return \str_replace(':reason', 'Patient must be created', $message);
                        }
                    );

                    return false;
                }
            }

            if (
                $stageId === PatientInquiryStage::getAppointmentScheduledId() || $stageId === PatientInquiryStage::getOnboardingCompleteId()
            ) {
                $validator->addReplacer(
                    'change_stage_requirements_are_met',
                    function ($message, $attribute, $rule, $parameters) {
                        return \str_replace(':reason', 'Patient must have an active appointment', $message);
                    }
                );

                return !is_null($inquiry->getFirstActiveAppointment());
            }

            return true;
        }, 'You cannot change inquiry stage due to following reason: :reason');
    }

    public function patientInsuranceExpires()
    {
        Validator::extend('patient_insurance_expires', function ($attribute, $value, $parameters, $validator) {
            $patient = Patient::find(request()->patient_id);

            return $patient->isInsuranceExpiring();
        }, 'Insurance reauthorization is not needed yet');
    }
    
    /**
     * @return void
     */
    public function appointmentReschedulingAccordingToPatientVisitFrequency()
    {
        Validator::extend('appointment_rescheduling_according_to_patient_visit_frequency', function ($attribute, $value, $parameters, $validator) {
            $appointment = Appointment::find(request()->id);

            if (!$appointment) {
                return false;
            }

            $patient = $appointment->patient;
            $date = Carbon::createFromTimestamp($appointment->time);
            $weekStart = $date->copy()->startOfWeek();

            if ($patient->visit_frequency_id === PatientVisitFrequency::getBiweeklyId()) {
                $date->modify('next week');
                $validator->addReplacer(
                    'appointment_rescheduling_according_to_patient_visit_frequency',
                    function ($message, $attribute, $rule, $parameters) {
                        return 'The date must be within the 2 week of the appointment time.';
                    }
                );
            };

            if ($patient->visit_frequency_id === PatientVisitFrequency::getMonthlyId()) {
                $date->modify('+3 weeks');
                $validator->addReplacer(
                    'appointment_rescheduling_according_to_patient_visit_frequency',
                    function ($message, $attribute, $rule, $parameters) {
                        return 'The date must be within the 4 week of the appointment time.';
                    }
                );
            };

            $weekEnd = $date->endOfWeek();

            return Carbon::parse($value)->between($weekStart, $weekEnd);
        }, 'The date must be within the week of the appointment time.');
    }

    /**
     * @return void
     */
    public function endTimeTreatmentModality()
    {
        Validator::extend('end_time_treatment_modality', function ($attribute, $value, $parameters, $validator) {
            $startTime = Carbon::createFromFormat('g:i A', request()->start_time);
            $endTime = Carbon::createFromFormat('g:i A', $value);
            $diffInMinutes = $startTime->diffInMinutes($endTime);

            $treatmentModality = TreatmentModality::find(request()->treatment_modality_id);

            $minDuration = $treatmentModality->min_duration;
            $maxDuration = $treatmentModality->max_duration;

            if (isset($minDuration) && isset($maxDuration)) {
                $validator->addReplacer(
                    'end_time_treatment_modality',
                    function ($message) use ($minDuration, $maxDuration) {
                        $message = str_replace(':minDuration', $minDuration, $message);
                        $message = str_replace(':maxDuration', $maxDuration, $message);
                        return $message;
                    }
                );
                return $diffInMinutes >= $minDuration && $diffInMinutes <= $maxDuration;
            }
            if (isset($minDuration)) {
                $validator->addReplacer(
                    'end_time_treatment_modality',
                    function () use ($minDuration) {
                        return "Session time should be at least $minDuration minutes for the selected treatment modality.";
                    }
                );
                return $diffInMinutes >= $minDuration;
            }

            return true;
        }, 'Session time should be between :minDuration and :maxDuration minutes for the selected treatment modality.');
    }

    public function cardBelongsToPatient()
    {
        Validator::extend('card_belongs_to_patient', function ($attribute, $value, $parameters, $validator) {
            $patient = $this->app->request->route('patient');

            return $patient->squareAccounts()
                ->rightJoin(
                    'patient_square_account_cards as psac',
                    'psac.patient_square_account_id',
                    '=',
                    'patient_square_accounts.id'
                )
                ->where('psac.id', $value)
                ->exists();
        }, "Selected credit card doesn't belong to the patient");
    }

    public function patientRequestedCancellationAt()
    {
        Validator::extend('patient_requested_cancellation_at', function ($attribute, $value, $parameters, $validator) {
            $appointment = Appointment::find(request()->appointmentId);

            $patientRequestedCancellationAt = Carbon::parse($value);
            $appointmentTime = Carbon::createFromTimestamp($appointment->time);

            return $patientRequestedCancellationAt->diffInMinutes($appointmentTime, false) > 1440; // 24 hours
        }, '"Cancelled by Patient" status could not be selected since there are less than 24 hours left before the start of appointment');
    }

    // @todo: Remove this custom rule when migrating to Laravel 5.6 or higher
    public function lte()
    {
        Validator::extend('lte', function ($attribute, $value, $parameters, $validator) {
            $comparedValue = $validator->attributes()[$parameters[0]];

            $validator->setCustomMessages([
                'lte' => "The $attribute field must be less than or equal to the $parameters[0] field.",
            ]);

            return $value <= $comparedValue;
        });
    }

    public function formHasBeenSent()
    {
        Validator::extend('form_has_been_sent', function ($attribute, $value, $parameters, $validator) {
            if (Auth::user()->isAdmin()) {
                return true;
            }
            
            $formType = PatientFormType::find($parameters[0]);
            if (!$formType) {
                return true;
            }

            if (array_search($formType->name, array_column($value, 'name')) === false) {
                return true;
            }
            
            $validator->setCustomMessages([
                'form_has_been_sent' => "You cannot send '{$formType->title}' intake form for this patient until it has been sent by the administrators.",
            ]);
            
            return PatientDocumentRequest::query()
                ->join('patient_document_request_items', 'patient_document_request_items.request_id', '=', 'patient_document_requests.id')
                ->where('patient_id', request()->patient->id)
                ->where('patient_document_request_items.form_type_id', $formType->id)
                ->exists();
        });
    }
}
