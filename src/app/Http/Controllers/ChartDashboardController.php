<?php

namespace App\Http\Controllers;

use App\Components\Salary\Salary;
use App\Patient;
use App\PatientStatus;
use App\PatientVisit;
use App\Provider;
use App\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChartDashboardController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getProviderMissingNotes() {
        $user = \Auth::user();
        $fromDate = Carbon::today()->subMonths(12);
        $archivedStatus = PatientStatus::getArchivedId();
        $statuses = PatientStatus::query()->where('id', '!=', $archivedStatus)->pluck('id')->toArray();

        $missingNotes = [];
        if ($user->isAdmin()) {
            $missingNotes = Provider::getPatientsWithMissingNotesGroupedByProviders($statuses, [], $fromDate);
        } else {
            $providerId = $user->provider_id;
            $missingNotes = Provider::getPatientsWithMissingNotes($providerId, $statuses, [], $fromDate);
        }

        return response($missingNotes);
    }

    public function getProviderMissingInitialAssessments() {
        $user = \Auth::user();
        $fromDate = Carbon::today()->subMonths(12);
        $archivedStatus = PatientStatus::getArchivedId();
        $statuses = PatientStatus::query()->where('id', '!=', $archivedStatus)->pluck('id')->toArray();

        $missingInitialAssessments = [];
        if ($user->isAdmin()) {
            $missingInitialAssessments = Provider::getPatientsWithMissingInitialAssessmentsGroupedByProviders($statuses, [], $fromDate);
        } else {
            $providerId = $user->provider_id;
            $missingInitialAssessments = Provider::getPatientsWithMissingInitialAssessments($providerId, $statuses, [], $fromDate);
        }

        return response($missingInitialAssessments);
    }

    public function getProviderMissingCopay()
    {
        $user = \Auth::user();

        $patients = Patient::select([
                'patients.id',
                DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"),
                'patients.status_id',
                'patients.primary_insurance',
            ])
            ->with([
                'preprocessedBalance' => function($query) {
                    $query->select([
                        'balance_after_transaction AS balance',
                        'patient_id',
                        'transactionable_id',
                        'transactionable_type',
                    ])->with('transactionable');
                },
                'status' => function($query) {
                    $query->select([
                        'id',
                        'hex_color',
                    ]);
                },
            ])
            ->where('patients.is_test', false)
            ->whereRaw("(
                SELECT balance_after_transaction
                FROM patient_preprocessed_transactions
                WHERE patient_id = patients.id
                ORDER BY id DESC
                LIMIT 1
            ) < 0")
            ->orderBy('patient_name');

        if ($user->isAdmin()) {
            $activeCompletedVisitCreatedStatusesId = Status::getActiveCompletedVisitCreatedStatusesId();
            $lastProviderSql = "
                SELECT providers.provider_name
                FROM appointments
                JOIN providers ON providers.id = appointments.providers_id
                WHERE appointments.patients_id = patients.id AND appointments.appointment_statuses_id IN (" . implode(',', $activeCompletedVisitCreatedStatusesId). ") AND appointments.deleted_at IS NULL
                ORDER BY appointments.time DESC
                LIMIT 1";
            $patients = $patients->addSelect(DB::raw("($lastProviderSql) AS provider_name"));
        } else {
            $providerId = $user->provider_id;
            $patients = $patients->whereHas('providers', function($query) use ($providerId) {
                $query->providerNames();
                $query->where('id', $providerId);
            });
        }

        $from = Carbon::now()->subMonths(3)->startOfDay();
        $patients = $patients->get()
            ->filter(function (Patient $patient) use ($from) {
                return $from->lte($patient->preprocessedBalance->transactionable->transaction_date);
            })
            ->each(function (Patient $patient) {
                // Set the visible attributes for the 'preprocessedBalance' relation to include only 'balance' and 'patient_id'
                // This ensures that only the 'balance' and 'patient_id' attributes are included in the response
                $patient->preprocessedBalance->setVisible(['balance', 'patient_id']);
            })
            ->values();

        return response($patients);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getReauthorizationRequests()
    {
        $user = Auth::user();
        $archivedId = PatientStatus::getArchivedId();
        $dischargedId = PatientStatus::getDischargedId();
        $patients = Patient::getPatientsWithUpcomingReauthorizationQuery()
            ->whereNotIn('patients.status_id', [$archivedId, $dischargedId]);

        if ($user->isAdmin()) {
            $activeCompletedVisitCreatedStatusesId = Status::getActiveCompletedVisitCreatedStatusesId();
            $lastProviderSql = "
                SELECT providers.provider_name
                FROM appointments
                JOIN providers ON providers.id = appointments.providers_id
                WHERE appointments.patients_id = patients.id AND appointments.appointment_statuses_id IN (" . implode(',', $activeCompletedVisitCreatedStatusesId). ") AND appointments.deleted_at IS NULL
                ORDER BY appointments.time DESC
                LIMIT 1";
            $patients = $patients->addSelect(DB::raw("($lastProviderSql) AS provider_name"));
        } else {
            $providerId = $user->provider_id;
            $patients = $patients->whereHas('providers', function ($query) use ($providerId) {
                $query->providerNames();
                $query->where('id', $providerId);
            });
        }
        
        $patients = $patients->get();

        return response($patients);
    }

    public function getAssignedPatients()
    {
        $provider = auth()->user()->provider;

        if (empty($provider)) {
            return response()->json();
        }

        $fullNameQuery = 'IF(middle_initial != "", CONCAT(first_name, " ", last_name, " ", middle_initial), CONCAT(first_name, " ", last_name))';

        $patients = $provider->patients()
            ->select([
                'id',
                'first_name',
                'last_name',
                'middle_initial',
                DB::raw("$fullNameQuery as full_name"),
                'status_id',
                'primary_insurance_id',
            ])
            // show test patients as well
            // ->where('patients.is_test', false)
            ->with([
                'status:id,status,hex_color',
                'insurance:id,insurance'
            ])
            ->orderByRaw('full_name')
            ->get();

        return response()->json($patients);
    }

    public function getInactivePatients()
    {
        $fullNameQuery = 'IF(patients.middle_initial != "", CONCAT(patients.first_name, " ", patients.last_name, " ", patients.middle_initial), CONCAT(patients.first_name, " ", patients.last_name))';

        $providerId = auth()->user()->provider_id;

        $patients = Patient::query()
            ->select([
                'patients.id',
                'patients.first_name',
                'patients.last_name',
                'patients.middle_initial',
                DB::raw("$fullNameQuery as full_name"),
                'patients.status_id',
                'patients.primary_insurance',
                'patients.primary_insurance_id',
            ])
            ->with([
                'status:id,status,hex_color',
                'insurance:id,insurance'
            ])
            ->statusInactive()
            ->where('patients.is_test', false)
            ->when(empty($providerId), function ($query) {
                $activeCompletedVisitCreatedStatusesId = Status::getActiveCompletedVisitCreatedStatusesId();
                $lastProviderSql = "
                    SELECT providers.provider_name
                    FROM appointments
                    JOIN providers ON providers.id = appointments.providers_id
                    WHERE appointments.patients_id = patients.id AND appointments.appointment_statuses_id IN (" . implode(',', $activeCompletedVisitCreatedStatusesId). ") AND appointments.deleted_at IS NULL
                    ORDER BY appointments.time DESC
                    LIMIT 1";
                $query->addSelect(DB::raw("($lastProviderSql) AS provider_name"));
            })
            ->when(!empty($providerId), function ($query) use($providerId) {
                $query->whereHas('providers', function ($providersQuery) use ($providerId) {
                    $providersQuery->where('id', $providerId);
                });
            })
            ->orderByRaw('full_name')
            ->get();

        return response()->json($patients);
    }

    public function getVisitsDatasetForChart() {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        $providerId = Auth::user()->isAdmin() ? null : Auth::user()->provider_id;
        $now = Carbon::now();
        $dateFrom = Carbon::now()->startOfMonth();
        $dateFromResponse = $dateFrom->format('j M, Y');
        $salaryHelper = new Salary($dateFrom, $now, $providerId);
        $salary = $salaryHelper->get();
//        $missingNotes = $salaryHelper->getMissingProgressNotes()->toArray();
        $missingNotes = [];
        $visitsData = PatientVisit::query()
            ->select([
                'date',
                \DB::raw('COUNT(`id`) AS c'),
            ])
            ->whereDate('date', '>=', $dateFrom->toDateString())
            ->whereDate('date', '<=', $now->toDateString())
            ->whereNotNull('provider_id')
            ->whereNotNull('patient_id')
            ->when(!is_null($providerId), function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->groupBy(['date'])
            ->pluck('c', 'date')
            ->toArray();
        $labels = [];
        $data = [];
        while($dateFrom->lte($now)) {
            $labels[] = $dateFrom->format('m/d/Y');
            $data[] = (int)data_get($visitsData, $dateFrom->toDateString());
            $dateFrom->addDay();
        }
        
        $total = [
            'visits' => 0,
            'pay' => 0,
        ];

        foreach($salary as $i) {
            foreach($i as $item) {
                if(!is_null($item->amount_paid)) {
                    $total['pay'] += $item->amount_paid;
                }
                if(!is_null($item->visits_per_month)) {
                    $total['visits'] += $item->visits_per_month;
                }
            }
        }
    
        foreach($missingNotes as $item) {
            if(!is_null($item) && array_key_exists('missing_pn_cost', $item) && array_key_exists('missing_note_count', $item)) {
                $total['pay'] += $item['missing_pn_cost'];
                $total['visits'] += $item['missing_note_count'];
            }
        }

        return response([
            'labels' => $labels,
            'data' => $data,
            'total' => $total,
            'date_from' => $dateFromResponse,
            'date_to' => $now->format('j M, Y'),
        ]);
    }
}
