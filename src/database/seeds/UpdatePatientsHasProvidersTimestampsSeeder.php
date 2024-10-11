<?php

use App\Models\PatientHasProvider;
use App\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePatientsHasProvidersTimestampsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PatientHasProvider::query()
            ->whereNull('created_at')
            ->orderBy('patients_id', 'desc')
            ->chunk(1000, function ($data) {
                foreach ($data as $patientHasProvider) {
                    // $this->updateWithLoggerAndFirstAppointment($patientHasProvider);

                    $this->updateWithComment($patientHasProvider);
                }
            });
    }

    private function updateWithComment($patientHasProvider)
    {
        $provider = $patientHasProvider->provider;

        if (!$provider) {
            // $patientHasProvider->delete();
            return;
        }

        $providerName = $provider->provider_name;

        $commentText = 'assigned <b class="label-blue bold">' . $providerName . '</b> to the patient.';
        $comment = $patientHasProvider->patient->comments()
            ->where('is_system_comment', 1)
            ->where('comment', 'like', '%' . $commentText . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($comment) {
            $patientHasProvider->update([
                'created_at' => $comment->created_at,
                'updated_at' => $comment->created_at,
            ]);
        }
    }

    private function updateWithLoggerAndFirstAppointment(PatientHasProvider $patientHasProvider): void
    {
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
                'updated_at' => $recordInLogger->appeared_at,
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
    }
}
