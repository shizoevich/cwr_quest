<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Jobs\Supervisors\SyncAttachedPatientsToSupervisor;
use App\Models\Patient\PatientTag;
use App\Models\Patient\PatientTransfer;
use App\Provider;
use App\Patient;
use App\Models\PatientHasProvider;
use App\Models\Provider\ProviderSupervisor;
use Carbon\Carbon;

class PatientHasProviderObserver
{
    public function created(PatientHasProvider $patientHasProvider)
    {
        $this->syncWithSupervisorIfNeeded($patientHasProvider);
        $this->closeTransferIfNeeded($patientHasProvider);

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientHasProvider),
                'event' => 'create',
                'data' => $patientHasProvider->getLogData(),
                'message' => $patientHasProvider->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientHasProvider $patientHasProvider) {
        $dirtyFields = $patientHasProvider->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientHasProvider),
                'event' => 'update',
                'data' => $patientHasProvider->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientHasProvider->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientHasProvider $patientHasProvider)
    {
        $this->syncWithSupervisorIfNeeded($patientHasProvider);
        $this->reattachToSupervisorIfNeeded($patientHasProvider);

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientHasProvider),
                'event' => 'delete',
                'data' => $patientHasProvider->getLogData(),
                'message' => $patientHasProvider->getDeleteLogMessage(),
            ]
        );
    }

    private function syncWithSupervisorIfNeeded(PatientHasProvider $patientHasProvider)
    {
        if ($patientHasProvider->chart_read_only) {
            return;
        }

        $providerSupervisor = ProviderSupervisor::getSupervisorForDate($patientHasProvider->providers_id, Carbon::now());
        if (!$providerSupervisor) {
            return;
        }

        dispatch(new SyncAttachedPatientsToSupervisor($providerSupervisor));
    }

    private function reattachToSupervisorIfNeeded(PatientHasProvider $patientHasProvider)
    {
        $provider = Provider::find($patientHasProvider->providers_id);
        if (!$provider || !$provider->is_supervisor) {
            return;
        }

        $superviseeIds = ProviderSupervisor::getSuperviseeForToday($provider->id)->pluck('provider_id');
        if (!count($superviseeIds)) {
            return;
        }

        $patient = Patient::find($patientHasProvider->patients_id);
        if (!$patient) {
            return;
        }

        $patientProvider = $patient->providers()->withTrashed()->whereIn('id', $superviseeIds)->first();
        if (empty($patientProvider)) {
            return;
        }

        PatientHasProvider::create([
            'patients_id' => $patient->id,
            'providers_id' => $provider->id,
            'supervisee_id' => $patientProvider->id,
            'chart_read_only' => true,
        ]);
    }

    private function closeTransferIfNeeded(PatientHasProvider $patientHasProvider): void
    {
        if ($patientHasProvider->chart_read_only) {
            return;
        }

        $patientTransfer = PatientTransfer::query()
            ->active()
            ->where('patient_id', $patientHasProvider->patients_id)
            ->first();

        if ($patientTransfer) {
            $patientTransfer->close();
        }
    }
}
