<?php

namespace App\Jobs\Supervisors;

use App\Models\PatientHasProvider;
use App\Models\Provider\ProviderSupervisor;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncAttachedPatientsToSupervisor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var ProviderSupervisor
     */
    private $providerSupervisor;

    /**
     * Create a new job instance.
     *
     * @param ProviderSupervisor $providerSupervisor
     */
    public function __construct(ProviderSupervisor $providerSupervisor)
    {
        $this->providerSupervisor = $providerSupervisor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->detachProviderPatientsFromSupervisor($this->providerSupervisor->supervisor_id, $this->providerSupervisor->provider_id);

        if ($this->shouldAttachPatientsToSupervisor()) {
            $this->attachProviderPatientsToSupervisor($this->providerSupervisor->supervisor_id, $this->providerSupervisor->provider);
        }
    }

    private function detachProviderPatientsFromSupervisor(int $supervisorId, int $providerId): void
    {
        PatientHasProvider::query()
            ->where('providers_id', $supervisorId)
            ->where('supervisee_id', $providerId)
            ->where('chart_read_only', true)
            ->delete();
    }

    private function shouldAttachPatientsToSupervisor()
    {
        $todayDate = Carbon::today()->toDateString();
        $attachedDate = Carbon::parse($this->providerSupervisor->attached_at)->toDateString();
        $detachedDate = $this->providerSupervisor->detached_at
            ? Carbon::parse($this->providerSupervisor->detached_at)->toDateString()
            : null;

        return ($attachedDate <= $todayDate) && (is_null($detachedDate) || ($detachedDate > $todayDate));
    }

    private function attachProviderPatientsToSupervisor(int $supervisorId, Provider $provider): void
    {
        $provider->patients()->each(function ($patient) use ($supervisorId, &$provider) {
            $patientHasProvider = PatientHasProvider::query()
                ->where('patients_id', $patient->id)
                ->where('providers_id', $supervisorId)
                ->first();

            if (!$patientHasProvider) {
                PatientHasProvider::create([
                    'patients_id' => $patient->id,
                    'providers_id' => $supervisorId,
                    'supervisee_id' => $provider->id,
                    'chart_read_only' => true,
                ]);
            } else if (!$patientHasProvider->supervisee_id && $patientHasProvider->chart_read_only) {
                $patientHasProvider->update([
                    'supervisee_id' => $provider->id,
                ]);
            }
        });
    }
}
