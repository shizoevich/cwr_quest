<?php

namespace App\Repositories\Provider;

use App\Jobs\Supervisors\SyncAttachedPatientsToSupervisor;
use App\Models\Provider\ProviderSupervisor;
use App\Provider;
use App\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SupervisorRepository implements SupervisorRepositoryInterface
{
    public function getSupervisors(): Collection 
    {
        return Provider::where('is_supervisor', true)->get();
    }

    public function getSupervisees(int $supervisorId): Collection
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();

        $today = Carbon::today()->toDateString();

        return ProviderSupervisor::query()
            ->where('supervisor_id', $supervisorId)
            ->whereDate('attached_at', '<=', $today)
            ->where(function ($query) use (&$today) {
                $query->whereNull('detached_at')
                    ->orWhereDate('detached_at', '>', $today);
            })
            ->whereHas('provider')
            ->with([
                'provider',
                'provider.user' => function ($query) {
                    $query->withTrashed()
                        ->select(['id', 'provider_id', 'deleted_at']);
                },
                'provider.patients' => function ($query) use ($appointmentStatuses) {
                    $query->select('patients.*')
                        ->addSelect('patients_has_providers.created_at as date_of_assignment')
                        ->withCount(['appointments' => function ($query) use ($appointmentStatuses) {
                            $query->whereIn('appointment_statuses_id', $appointmentStatuses);
                        }])
                        ->with(['status', 'insurance', 'lastAppointment' => function ($query) use ($appointmentStatuses) {
                            $query->whereIn('appointment_statuses_id', $appointmentStatuses);
                        }]);
                }
            ])
            ->get();
    }

    public function storeIsSupervisor(bool $isSupervisor, Provider $provider): void
    {
        $provider->update([
            'is_supervisor' => $isSupervisor
        ]);

        if ($isSupervisor) {
            $providerSupervisor = ProviderSupervisor::getSupervisorForDate($provider->id, Carbon::now());
            if (isset($providerSupervisor)) {
                $providerSupervisor->update([
                    'detached_at' => Carbon::now()->toDateTimeString()
                ]);
                \Bus::dispatchNow(new SyncAttachedPatientsToSupervisor($providerSupervisor));
            }
        }
    }

    public function checkSuperviseeLimit(int $supervisorId): bool
    {
        $supervisees = ProviderSupervisor::getSuperviseeForToday($supervisorId);

        return count($supervisees) < config('supervisor.supervisee_limit');
    }

    public function attachSupervisor(array $data, Provider $provider): void
    {
        $supervisorId = $data['supervisor_id'];

        $date = Carbon::now();
        if (isset($data['date'])) {
            $date = Carbon::parse($data['date']);
        }

        // update current supervisor
        $providerSupervisor = ProviderSupervisor::getSupervisorForDate($provider->id, Carbon::now());
        if (isset($providerSupervisor)) {
            $providerSupervisor->update([
                'detached_at' => $date->toDateTimeString()
            ]);
        }

        // remove all supervisors assigned after today
        ProviderSupervisor::query()
            ->where('provider_id', $provider->id)
            ->whereDate('attached_at', '>', Carbon::now()->toDateString())
            ->delete();

        if ($supervisorId) {
            $providerSupervisor = ProviderSupervisor::create([
                'provider_id' => $provider->id,
                'supervisor_id' => $supervisorId,
                'attached_at' => $date->toDateTimeString()
            ]);
        }

        if ($date->isToday()) {
            \Bus::dispatchNow(new SyncAttachedPatientsToSupervisor($providerSupervisor));
        }
    }
}
