<?php

namespace App\Http\Controllers\Api\Provider;

use App\Appointment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\Salary\Timesheet\Admin\Complete;
use App\Http\Requests\Provider\Salary\Timesheet\Admin\Index;
use App\Http\Requests\Provider\Salary\Timesheet\Admin\Show;
use App\Jobs\Salary\SyncSalaryData;
use App\Models\Billing\BillingPeriod;
use App\Models\Provider\Salary;
use App\Models\Provider\SalaryTimesheet;
use App\Models\Provider\SalaryTimesheetLateCancellation;
use App\Models\Provider\SalaryTimesheetSickTime;
use App\Models\Provider\SalaryTimesheetSupervision;
use App\Models\Provider\SalaryTimesheetVisit;
use App\PatientInsuranceProcedure;
use App\PatientVisit;
use App\Provider;
use App\Repositories\Provider\Salary\Timesheet\TimesheetRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;

class AdminSalaryTimesheetController extends Controller 
{
    /**
     * @var TimesheetRepositoryInterface
     */
    protected $timesheetRepository;

    /**
     * AppointmentController constructor.
     * @param TimesheetRepositoryInterface $timesheetRepository
     */
    public function __construct(TimesheetRepositoryInterface $timesheetRepository)
    {
        $this->timesheetRepository = $timesheetRepository;
    }

    public function index(Index $request)
    {
        $billingPeriod = BillingPeriod::find($request->input('billing_period_id'));

        return Provider::query()
            ->select([
                'salary_timesheets.id',
                'providers.id AS provider_id',
                'providers.provider_name',
                'salary_timesheets.signed_at',
                'salary_timesheets.reviewed_at',
                \DB::raw("(Select COUNT(*) FROM salary_timesheet_visits where salary_timesheet_visits.provider_id=providers.id 
                    AND salary_timesheet_visits.billing_period_id=".$billingPeriod->getKey().") AS visits_count"),
                \DB::raw("(SELECT COUNT(*) FROM salary_timesheet_late_cancellations WHERE salary_timesheet_late_cancellations.provider_id=providers.id 
                    AND salary_timesheet_late_cancellations.billing_period_id=".$billingPeriod->getKey().") AS late_cancellations_count"),
                \DB::raw("IF(
                    `providers`.`is_supervisor` = 1,
                    (
                        SELECT SUM(supervision_hours)
                        FROM salary_timesheet_supervisions
                        WHERE `salary_timesheet_supervisions`.`supervisor_id`=`providers`.`id`
                            AND `salary_timesheet_supervisions`.`billing_period_id`={$billingPeriod->getKey()}
                    ),
                    (
                        SELECT SUM(supervision_hours)
                        FROM salary_timesheet_supervisions
                        WHERE `salary_timesheet_supervisions`.`provider_id`=`providers`.`id`
                            AND `salary_timesheet_supervisions`.`billing_period_id`={$billingPeriod->getKey()}
                    )
                ) AS supervision_hours_sum")
            ])
            //            ->leftJoin('salary_timesheets', 'salary_timesheets.provider_id', 'providers.id')
            ->leftJoin('salary_timesheets', function (JoinClause $join) use ($billingPeriod) {
                $join->on('salary_timesheets.provider_id', '=', 'providers.id')
                    ->where('salary_timesheets.billing_period_id', $billingPeriod->getKey());
            })
            ->where(function ($query) use ($billingPeriod) {
                $query->where(function ($query) use ($billingPeriod) {
                    $query->whereNull('salary_timesheets.id')->where('providers.billing_period_type_id', $billingPeriod->type_id);
                })->orWhere('salary_timesheets.billing_period_id', $billingPeriod->getKey());
            })
            ->havingRaw('visits_count > 0 OR late_cancellations_count > 0 OR supervision_hours_sum > 0')
            ->orderBy('providers.provider_name')
            ->get();
    }

    public function get(Show $request, SalaryTimesheet $salaryTimesheet)
    {
        $visits = SalaryTimesheetVisit::query()
            ->select([
                'salary_timesheet_visits.*',
                'appointments.id AS appointment_id',
                'appointments.is_initial',
                'appointments.note_on_paper',
                'appointments.progress_note_complete',
                'appointments.initial_assessment_id',
                'appointments.initial_assessment_created_at',
                'patient_notes.id AS patient_note_id',
                'patient_notes.finalized_at AS note_finalized_at',
                \DB::raw("IF(`appointments`.`time` IS NULL, 1, 0) AS ordering"),
            ])
            ->join('patients', 'patients.id', '=', 'salary_timesheet_visits.patient_id')
            ->leftJoin('patient_visits', 'patient_visits.id', '=', 'salary_timesheet_visits.visit_id')
            ->leftJoin('appointments', 'appointments.id', '=', 'patient_visits.appointment_id')
            ->leftJoin('patient_notes', function ($join) {
                $join->on('patient_notes.appointment_id', '=', 'appointments.id')
                    ->whereNull('patient_notes.deleted_at');
            })
            ->with('patient:id,first_name,last_name,middle_initial')
            ->when($request->input('show_only_therapist_changes'), function ($query) {
                $query->where('is_custom_created', true);
            })
            ->where('salary_timesheet_visits.provider_id', $salaryTimesheet->provider_id)
            ->where('salary_timesheet_visits.billing_period_id', $salaryTimesheet->billing_period_id)
            ->orderBy('date')
            ->orderBy('ordering')
            ->orderBy('appointments.time')
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->get()
            ->transform(function ($timesheetVisit) {
                if (!$timesheetVisit->appointment_id
                    || $timesheetVisit->note_on_paper 
                    || $timesheetVisit->initial_assessment_id
                    || $timesheetVisit->progress_note_complete
                ) {
                    $timesheetVisit->is_progress_note_missing = false;
                } else {
                    $docBillingPeriod = null;
                    if ($timesheetVisit->note_finalized_at) {
                        $docBillingPeriod = BillingPeriod::getBillingPeriodByDate(Carbon::parse($timesheetVisit->note_finalized_at), $timesheetVisit->provider->billing_period_type_id);
                    } else if ($timesheetVisit->initial_assessment_created_at) {
                        $docBillingPeriod = BillingPeriod::getBillingPeriodByDate(Carbon::parse($timesheetVisit->initial_assessment_created_at), $timesheetVisit->provider->billing_period_type_id);
                    }

                    $timesheetVisit->is_progress_note_missing = !$docBillingPeriod || $docBillingPeriod->getKey() != $timesheetVisit->billing_period_id;
                }

                return $timesheetVisit;
            });
        $lateCancellations = SalaryTimesheetLateCancellation::query()
            ->with('patient:id,first_name,last_name,middle_initial')
            ->where('provider_id', $salaryTimesheet->provider_id)
            ->where('billing_period_id', $salaryTimesheet->billing_period_id)
            ->when($request->input('show_only_therapist_changes'), function ($query) {
                $query->where('is_custom_created', true);
            })
            ->get();
        
        $provider = Provider::query()->select(['id', 'provider_name', 'is_supervisor'])->find($salaryTimesheet->provider_id);
        
        $supervisions = [];
        if (optional($provider)->is_supervisor) {
            $supervisions = SalaryTimesheetSupervision::query()
                ->select([
                    'salary_timesheet_supervisions.provider_id',
                    'providers.provider_name',
                    'salary_timesheet_supervisions.supervisor_id',
                    'salary_timesheet_supervisions.supervision_hours',
                    'salary_timesheet_supervisions.comment'
                ])
                ->join('providers', 'providers.id', '=', 'salary_timesheet_supervisions.provider_id')
                ->where('supervisor_id', $salaryTimesheet->provider_id)
                ->where('billing_period_id', $salaryTimesheet->billing_period_id)
                ->get();
        }

        $patientNameQuery = 'IF(middle_initial != "", CONCAT(first_name, " ", last_name, " ", middle_initial), CONCAT(first_name, " ", last_name))';

        $sickTimes = SalaryTimesheetSickTime::query()
            ->with([
                'appointments:id,time,patients_id,visit_length',
                'appointments.patient' => function ($query) use ($patientNameQuery) {
                    $query->select([
                       'id',
                       'first_name',
                       'last_name',
                       'middle_initial',
                       \DB::raw("$patientNameQuery as patient_name")
                    ]);
                },
            ])
            ->where('provider_id', $salaryTimesheet->provider_id)
            ->where('billing_period_id', $salaryTimesheet->billing_period_id)
            ->get();

        return response()->json([
            'billing_period' => BillingPeriod::query()->with('type')->find($salaryTimesheet->billing_period_id),
            'provider' => $provider,
            'visits' => $visits,
            'late_cancellations' => $lateCancellations,
            'timesheet' => $salaryTimesheet,
            'timesheet1' => $salaryTimesheet,
            'supervisions' => $supervisions,
            'sick_times' => $sickTimes,
        ]);
    }

    public function acceptVisit(SalaryTimesheetVisit $timesheetVisit)
    {
        $visit = $timesheetVisit->visit()->withTrashed()->first();
        if ($visit) {
            $visit->update([
                'is_overtime' => $timesheetVisit->is_overtime,
                'needs_update_salary' => true,
                'is_telehealth' => $timesheetVisit->is_telehealth,
            ]);
        } else {
            $providerTariffPlanId = optional(\DB::table('providers_tariffs_plans')->where('provider_id', $timesheetVisit->provider_id)->first())->tariff_plan_id;
            $patientPlanId = $timesheetVisit->patient->insurance_plan_id;
            $procedureId = optional($visit->appointment->treatmentModality)->insurance_procedure_id;

            $timesheetVisit->patient->load(['insurancePlan.insurances']);
            $visit = PatientVisit::query()
                ->create([
                    'visit_id' => null,
                    'appointment_id' => null,
                    'patient_id' => $timesheetVisit->patient_id,
                    'provider_id' => $timesheetVisit->provider_id,
                    'provider_tariff_plan_id' => $providerTariffPlanId,
                    'insurance_id' => data_get($timesheetVisit->patient, 'insurancePlan.insurances.id'),
                    'plan_id' => $patientPlanId,
                    'procedure_id' => $procedureId,
                    'is_telehealth' => $timesheetVisit->is_telehealth,
                    'date' => $timesheetVisit->date,
                    'needs_update_salary' => true,
                    'is_update_salary_enabled' => true,
                    'is_overtime' => $timesheetVisit->is_overtime,
                    'salary_timesheet_visit_id' => $timesheetVisit->getKey(),
                ]);
        }
        \Bus::dispatchNow(new SyncSalaryData());
        $timesheetVisit->update(['accepted_at' => Carbon::now(), 'visit_id' => $visit->getKey()]);
        $timesheetVisit->load('patient:id,first_name,last_name,middle_initial');

        return response()->json(['data' => $timesheetVisit]);
    }

    public function declineVisit(SalaryTimesheetVisit $timesheetVisit)
    {
        $timesheetVisit->update(['declined_at' => Carbon::now()]);
        $timesheetVisit->load('patient:id,first_name,last_name,middle_initial');

        return response()->json(['data' => $timesheetVisit]);
    }

    public function acceptLateCancellation(SalaryTimesheetLateCancellation $timesheetLateCancellation)
    {
        $fee = $timesheetLateCancellation->getOriginal('amount') * Salary::LATE_CANCELLATION_PROVIDER_PERCENTAGE / 100;
        Salary::query()
            ->create([
                'provider_id' => $timesheetLateCancellation->provider_id,
                'type' => Salary::TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION,
                'fee' => $fee,
                'paid_fee' => $fee,
                'billing_period_id' => $timesheetLateCancellation->billing_period_id,
                'date' => $timesheetLateCancellation->date,
                'additional_data' => [
                    'salary_timesheet_late_cancellation_id' => $timesheetLateCancellation->getKey(),
                ]
            ]);
        $timesheetLateCancellation->update(['accepted_at' => Carbon::now()]);
        $timesheetLateCancellation->load('patient:id,first_name,last_name,middle_initial');

        return response()->json(['data' => $timesheetLateCancellation]);
    }

    public function declineLateCancellation(SalaryTimesheetLateCancellation $timesheetLateCancellation)
    {
        $timesheetLateCancellation->update(['declined_at' => Carbon::now()]);
        $timesheetLateCancellation->load('patient:id,first_name,last_name,middle_initial');

        return response()->json(['data' => $timesheetLateCancellation]);
    }

    public function completeTimesheet(Complete $request, SalaryTimesheet $salaryTimesheet)
    {
        $sickTimeHoursSum = 0;

        if ($request->input('monthly_meeting_attended')) {
            Salary::query()
                ->updateOrCreate([
                    'provider_id' => $salaryTimesheet->provider_id,
                    'billing_period_id' => $salaryTimesheet->billing_period_id,
                    'type' => Salary::TYPE_MONTHLY_MEETING_ATTENDANCE_COMPENSATION,
                ], [
                    'fee' => Salary::MONTHLY_MEETING_ATTENDANCE_PRICE,
                    'paid_fee' => Salary::MONTHLY_MEETING_ATTENDANCE_PRICE,
                    'date' => Carbon::parse($salaryTimesheet->created_at)->toDateString(),
                    'additional_data' => ['visit_count' => null],
                ]);
        }

        if (count($request->input('sick_times'))) {
            $sickTimeAppointmentsId = [];

            foreach ($request->input('sick_times') as $sickTime) {
                $salaryTimesheetSickTime = SalaryTimesheetSickTime::firstOrCreate([
                    'provider_id' => $salaryTimesheet->provider_id,
                    'billing_period_id' => $salaryTimesheet->billing_period_id,
                    'date' => $sickTime['date'],
                ]);

                $sickTimeAppointmentsId = array_merge($sickTimeAppointmentsId, $sickTime['appointments']);

                $salaryTimesheetSickTime->appointments()->sync($sickTime['appointments']);
            }

            $sickTimeHoursSum = Appointment::query()
                ->whereIn('id', $sickTimeAppointmentsId)
                ->sum('visit_length') / 60;

            if ($sickTimeHoursSum > 0) {
                // $fee = $sickTimeHoursSum * Salary::SICK_TIME_PRICE;
                $fee = 0;
                Salary::query()
                    ->updateOrCreate([
                        'provider_id' => $salaryTimesheet->provider_id,
                        'billing_period_id' => $salaryTimesheet->billing_period_id,
                        'type' => Salary::TYPE_SICK_TIME,
                    ], [
                        'fee' => $fee,
                        'paid_fee' => $fee,
                        'date' => Carbon::parse($salaryTimesheet->created_at)->toDateString(),
                        'additional_data' => ['visit_count' => $sickTimeHoursSum],
                    ]);
            }
        }

        $supervisions = $request->get('supervisions');
        if (isset($supervisions) && count($supervisions)) {
            $this->timesheetRepository->modifySupervisions($salaryTimesheet->billing_period_id, $supervisions);
        }
        
        if (optional($salaryTimesheet->provider)->is_supervisor) {
            $this->timesheetRepository->calcSupervisorCompensation($salaryTimesheet);
            $this->timesheetRepository->calcSuperviseeCompensation($salaryTimesheet);
        }

        $salaryTimesheet->update([
            'reviewed_at' => Carbon::now(),
            'seek_time' => $sickTimeHoursSum,
            'monthly_meeting_attended' => $request->input('monthly_meeting_attended'),
        ]);
    }

    public function deleteLateCancellationsFromTimeSheets(int $id)
    {
        return $this->timesheetRepository->deleteLateCancellationsFromTimeSheets($id);
    }
}
