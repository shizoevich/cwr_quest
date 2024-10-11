<?php

namespace App\Repositories\Provider;

use App\Patient;
use App\Provider;
use App\Option;
use App\Status;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class ProviderRepository implements ProviderRepositoryInterface
{
    const DEFAULT_PAGINATION = 20;

    /**
     * @param int         $limit
     * @param string|null $searchQuery
     * @param array       $filters
     *
     * @return LengthAwarePaginator
     */
    public function all(int $limit, $searchQuery, array $filters = []): LengthAwarePaginator
    {
        $query = Provider::query()
            ->select([
                'id', 'provider_name'
            ])->when($searchQuery, function ($query, $searchQuery) {
                $query->where('provider_name', 'like', "%{$searchQuery}%");
            })->when(!empty(data_get($filters, 'diagnoses')), function ($query) use ($filters) {
                $query->where(function($query) use ($filters) {
                    $query->whereHas('progressNotes', function($query) use ($filters) {
                        $query->whereHas('diagnoses', function($query) use ($filters) {
                            $query->whereIn('diagnoses.id', $filters['diagnoses']);
                        });
                    })->orWhereHas('electronicDocuments', function($query) use ($filters) {
                        $query->whereHas('diagnoses', function($query) use ($filters) {
                            $query->whereIn('diagnoses.id', $filters['diagnoses']);
                        });
                    });
                });
            });

        $pagination = empty($limit) ? self::DEFAULT_PAGINATION : $limit;

        return $query->orderBy('provider_name')
            ->paginate($pagination);
    }
    
    /**
     * @param Provider|null $provider
     *
     * @return string|null
     */
    public function billingPeriodName($provider): ?string
    {
        if($provider) {
            return \Cache::remember("provider:{$provider->getKey()}:billing-period-name", 5, function() use ($provider) {
                return optional($provider->billingPeriodType)->name;
            });
        }
        
        return null;
    }
    
    /**
     * @param Provider|null $provider
     *
     * @return bool
     */
    public function isBiWeeklyType($provider): bool
    {
        return $this->billingPeriodName($provider) === 'bi_weekly';
    }

    public function getTherapistCustomTimesheetOption()
    {
        $therapist_custom_timesheet = Option::where('option_name', 'therapist_custom_timesheet')->first()->option_value;

        if($therapist_custom_timesheet === "false"){
            return [
                'therapist_custom_timesheet' => false
             ];
        }elseif($therapist_custom_timesheet === "true"){
            return [
                'therapist_custom_timesheet' => true
             ];
        } else{
            return [
                'therapist_custom_timesheet' => false
             ];
        }
    }

    public function getPatientsByProvider(Provider $provider, array $data): Collection
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
        $appointmentStatusesImploded = implode(',', $appointmentStatuses);
        $appointmentStatusActive = Status::getActiveId();

        $fullNameQuery = 'IF(middle_initial != "", CONCAT(first_name, " ", last_name, " ", middle_initial), CONCAT(first_name, " ", last_name))';
        $lastVisitDateQuery = "SELECT FROM_UNIXTIME(appointments.time) FROM appointments 
                                        WHERE appointments.patients_id = patients.id 
                                          AND appointments.providers_id = $provider->id
                                          AND appointment_statuses_id IN ($appointmentStatusesImploded)
                                          AND appointments.deleted_at IS NULL 
                                        ORDER BY appointments.time DESC LIMIT 1";
        $nextAppointmentDateQuery = "SELECT FROM_UNIXTIME(appointments.time) FROM appointments 
                                        WHERE appointments.patients_id = patients.id
                                          AND appointments.providers_id = $provider->id
                                          AND appointment_statuses_id = $appointmentStatusActive
                                          AND appointments.deleted_at IS NULL 
                                        ORDER BY appointments.time DESC LIMIT 1";

        return Patient::query()
            ->whereHas('providers', function ($query) use ($provider) {
                $query->withTrashed()->where('id', $provider->id);
            })
            ->select([
                'id',
                DB::raw("$fullNameQuery as full_name"),
                'first_name',
                'last_name',
                'middle_initial',
                'date_of_birth',
                'sex',
                'primary_insurance_id',
                'status_id',
                DB::raw("($lastVisitDateQuery) as last_visit_date"),
                DB::raw("($nextAppointmentDateQuery) as next_appointment_date"),
            ])
            ->with([
                'status:id,status,hex_color',
                'insurance:id,insurance',
                'patientHasProviders',
                'patientHasProviders.provider' => function ($query) {
                    $query->select(['id', 'provider_name'])->withTrashed();
                },
            ])
            ->withCount([
                'appointments as visits_count' => function ($query) use ($provider, $appointmentStatuses) {
                    $query->whereIn('appointment_statuses_id', $appointmentStatuses)
                        ->where('providers_id', $provider->id);
                },
                'patientNotes as drafts_count' => function ($query) use ($provider) {
                    $query->onlyNotFinalized()->where('provider_id', $provider->id);
                },
                'appointments as missing_notes_count' => function ($query) use ($provider, $appointmentStatuses) {
                    $query
                        ->where('providers_id', $provider->id)
                        ->leftJoin('patient_notes', function(JoinClause $join) {
                            $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                                ->whereNull($join->table . '.deleted_at');
                        })
                        ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
                        ->where('appointments.note_on_paper', '=', 0)
                        ->where('appointments.is_initial', '=', 0)
                        ->whereNull('appointments.initial_assessment_id')
                        ->where(function(Builder $query) {
                            $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
                        });
                },
                'appointments as upcoming_appointments_count' => function ($query) use ($provider) {
                    $query
                        ->onlyActive()
                        ->where('providers_id', $provider->id)
                        ->where('time', '>', now()->timestamp);
                },
            ])
            ->when(! empty($data['search_query']), function ($query) use ($data) {
                $query->where('provider_name', 'like', "%{$data['search_query']}%");
            })
            ->orderByRaw('full_name')
            ->get();
    }
}