<?php

namespace App\Traits\Patient;

use App\Events\NeedsWriteSystemComment;
use App\Models\PatientHasProvider;
use App\Patient;
use App\Provider;

trait PatientProvider
{
    /**
     * @param Provider $provider
     * @param Patient $patient
     * @return bool
     */
    protected function connectProvider(Provider $provider, Patient $patient): bool
    {
        $patientHasProvider = PatientHasProvider::where('patients_id', $patient->id)
                                ->where('providers_id', $provider->id)
                                ->first();

        $comment = trans('comments.provider_assigned_automatically', [
            'provider_name' => $provider->provider_name,
        ]);

        if ($patientHasProvider && $patientHasProvider->chart_read_only) {
            $patientHasProvider->update(['chart_read_only' => false]);

            event(new NeedsWriteSystemComment($patient->id, $comment));

            return true;
        }
        if (!$patientHasProvider) {
            PatientHasProvider::create([
                'patients_id' => $patient->id,
                'providers_id' => $provider->id
            ]);

            event(new NeedsWriteSystemComment($patient->id, $comment));

            return true;
        }

        return false;
    }
}