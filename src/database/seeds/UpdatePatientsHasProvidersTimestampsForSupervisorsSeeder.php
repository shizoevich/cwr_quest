<?php

use App\Models\PatientHasProvider;
use App\Models\Provider\ProviderSupervisor;
use App\Appointment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePatientsHasProvidersTimestampsForSupervisorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $today = Carbon::today()->toDateString();

        ProviderSupervisor::query()
            ->whereDate('attached_at', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('detached_at')
                    ->orWhereDate('detached_at', '>', $today);
            })
            ->join('providers', function ($join) {
                $join->on('providers.id', '=', 'provider_supervisors.provider_id')
                    ->whereNull('providers.deleted_at');
            })
            ->each(function ($providerSupervisor) {
                $providerSupervisor->provider->patients()->each(function ($patient) use ($providerSupervisor) {
                    $patientHasProvider = PatientHasProvider::query()
                        ->where('patients_id', $patient->id)
                        ->where('providers_id', $providerSupervisor->provider_id)
                        ->where('chart_read_only', false)
                        ->first();

                    if (isset($patientHasProvider->created_at)) {
                        return;
                    }

                    $chartReadOnly = (bool) $patientHasProvider->chart_read_only;
                    $recordInLogger = DB::connection('mysql_logger')
                        ->table('hipaa_log_item')
                        ->where('data', 'like', '%"patients_id":' . $patientHasProvider->patients_id . ',"providers_id":' . $patientHasProvider->providers_id . ',"chart_read_only":' . $chartReadOnly . '%')
                        ->where('collection_name_id', 56) // PatientHasProvider collection_name_id
                        ->where('event_name_id', 278) // PatientHasProvider create event_name_id
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (isset($recordInLogger)) {
                        $patientHasProvider->update([
                            'created_at' => $recordInLogger->appeared_at,
                            'updated_at' => $recordInLogger->appeared_at
                        ]);
                    } else {
                        $firstAppointment = Appointment::query()
                            ->select(['id', 'created_at'])
                            ->where('patients_id', $patientHasProvider->patients_id)
                            ->where('providers_id', $patientHasProvider->providers_id)
                            ->orderBy('created_at', 'asc')
                            ->first();

                        if (isset($firstAppointment)) {
                            $patientHasProvider->update([
                                'created_at' => $firstAppointment->created_at,
                                'updated_at' => $firstAppointment->created_at,
                            ]);
                        }
                    }
                });
            });
    }
}
