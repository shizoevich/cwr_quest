<?php

namespace App\Repositories\PatientHasProvider;

use App\Events\NeedsWriteSystemComment;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\RetryJobQueueHelper;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Officeally\Retry\RetryDeleteUpcomingAppointments;
use App\Models\Patient\PatientTransfer;
use App\Models\PatientHasProvider;
use App\Option;
use App\Patient;
use App\Provider;
use Carbon\Carbon;

class PatientHasProviderRepository implements PatientHasProviderRepositoryInterface
{
    public function deletePatientProviderRelationship(array $data): bool
    {
        $account = Option::OA_ACCOUNT_1;
        $officeAllyHelper = new OfficeAllyHelper($account);
        $patient = Patient::findOrFail($data['patientId']);
        $provider = Provider::withTrashed()->findOrFail($data['providerId']);

        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        if (! empty($data['unassignAllProviders'])) {
            $patient->providers()
                ->withTrashed()
                ->where('id', '!=', $data['providerId'])
                ->each(function ($item) use ($data) {
                    $this->deletePatientProviderRelationship([
                        'patientId' => $data['patientId'],
                        'providerId' => $item->id,
                        'reason' => $data['reason'] ?? null,
                    ]);
                });
        }

        if ($patient->patient_id) {
            try {
                $officeAllyHelper->deleteUpcomingAppointments($patient->patient_id, $provider->officeally_id);
            } catch (OfficeallyAuthenticationException $e) {
                $job = (new RetryDeleteUpcomingAppointments($patient->id, $account, $provider->officeally_id))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryDeleteUpcomingAppointments($patient->id, $account, $provider->officeally_id))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }
        /**
         * unassign primary care provider
         */
        $dataForUpdate = [
            'new_primary_care_provider' => null,
            'delete_primary_care_provider' => $provider->officeally_id,
        ];

        RetryJobQueueHelper::dispatchRetryUpdatePatient($account, $dataForUpdate, $patient->id);

        $status = 0;
        $patientHasProvider = PatientHasProvider::query()
            ->where('patients_id', $data['patientId'])
            ->where('providers_id', $data['providerId'])
            ->first();

        if ($patientHasProvider) {
            $status = $patientHasProvider->delete();

            if ($patient->providers()->withTrashed()->doesntExist() && $patient->transfers()->active()->doesntExist()) {
                PatientTransfer::create([
                    'patient_id' => $patient->id,
                    'old_provider_id' => $provider->id,
                    'created_by' => auth()->id(),
                    'closed_at' => null,
                    'unassigned_at' => now(),
                ]);
            }
        }

        $user = auth()->user();
        $comment = empty($data['reason'])
            ? trans('comments.admin_unassigned_provider', [
                'admin_name' => $user->meta->firstname . ' ' . $user->meta->lastname,
                'provider_name' => Provider::withTrashed()->where('id', $data['providerId'])->first()->provider_name,
            ])
            : trans('comments.admin_unassigned_provider_with_reason', [
                'admin_name' => $user->meta->firstname . ' ' . $user->meta->lastname,
                'provider_name' => Provider::withTrashed()->where('id', $data['providerId'])->first()->provider_name,
                'reason' => $data['reason'],
            ]);

        event(new NeedsWriteSystemComment($data['patientId'], $comment));

        return $status;
    }

    public function addPatientProviderRelationship(array $data): PatientHasProvider
    {
        $model = PatientHasProvider::query()
            ->where('providers_id', $data['providerId'])
            ->where('patients_id', $data['patientId'])
            ->where('chart_read_only', false)
            ->first();

        if (!is_null($model)) {
            return $model;
        }

        $model = PatientHasProvider::updateOrCreate([
            'providers_id' => $data['providerId'],
            'patients_id' => $data['patientId'],
        ], ['chart_read_only' => false]);

        $user = auth()->user();
        $comment = trans('comments.admin_assigned_provider', [
            'admin_name' => $user->meta->firstname . ' ' . $user->meta->lastname,
            'provider_name' => Provider::withTrashed()->where('id', $data['providerId'])->first()->provider_name,
        ]);
        event(new NeedsWriteSystemComment($data['patientId'], $comment));

        return $model;
    }
}