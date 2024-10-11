<?php

namespace App\Repositories\Patient;

use App\Events\NeedsWriteSystemComment;
use App\Events\Patient\RemovalRequestListUpdated;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\RetryJobQueueHelper;
use App\Jobs\Officeally\Retry\RetryDeleteUpcomingAppointments;
use App\Models\Patient\PatientRemovalRequest;
use App\Option;
use App\Models\PatientHasProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PatientRemovalRequestRepository implements PatientRemovalRequestRepositoryInterface
{
    const CHECKED_PER_PAGE = 15;

    public function getList(array $data): array
    {
        $data = PatientRemovalRequest::query()
            ->select()
            ->addSelectStatusText()
            ->with([
                'patient' => function ($query) {
                    $query->select([
                        'id',
                        'status_id',
                        'first_name',
                        'last_name',
                        'middle_initial',
                        DB::raw("CONCAT(first_name, ' ', last_name, ' ', middle_initial) AS full_name"),
                    ]);
                    $query->with([
                        'status' => function ($query) {
                            $query->select([
                                'id',
                                'hex_color',
                            ]);
                        },
                    ]);
                },
                'provider' => function ($query) {
                    $query->withTrashed()
                        ->select([
                            'id',
                            'provider_name',
                        ]);
                },
                'approver' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'meta' => function ($query) {
                            $query->select([
                                'user_id',
                                DB::raw("CONCAT(firstname, ' ', lastname) AS full_name"),
                            ]);
                        },
                    ]);
                },
            ])
            ->when(! empty($data['request_statuses']), function ($query) use ($data) {
                $query->whereIn('status', $data['request_statuses']);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ],
        ];
    }

    public function getNewRequests(): Collection
    {
        return PatientRemovalRequest::query()
            ->new()
            ->with([
                'patient' => function ($query) {
                    $query->select([
                        'id',
                        'status_id',
                        DB::raw("CONCAT(first_name, ' ', last_name) AS name"),
                    ]);
                    $query->with([
                        'status' => function ($query) {
                            $query->select([
                                'id',
                                'hex_color',
                            ]);
                        },
                    ]);
                },
                'provider' => function ($query) {
                    $query->select([
                        'id',
                        'provider_name AS name',
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCheckedRequests(): LengthAwarePaginator
    {
        return PatientRemovalRequest::query()
            ->select([
                '*',
                DB::raw("IF(status = " . PatientRemovalRequest::STATUS_ACCEPTED .
                    ", 'Approved', IF(status = " . PatientRemovalRequest::STATUS_DECLINED . ", 'Declined', 'Canceled')) AS status_text"),
            ])
            ->checked()
            ->with([
                'patient' => function ($query) {
                    $query->select([
                        'id',
                        'status_id',
                        DB::raw("CONCAT(first_name, ' ', last_name) AS name"),
                    ]);
                    $query->with([
                        'status' => function ($query) {
                            $query->select([
                                'id',
                                'hex_color',
                            ]);
                        },
                    ]);
                },
                'provider' => function ($query) {
                    $query->select([
                        'id',
                        'provider_name AS name',
                    ]);
                },
                'approver' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'meta' => function ($query) {
                            $query->select([
                                'user_id',
                                DB::raw("CONCAT(firstname, ' ', lastname) AS name"),
                            ]);
                        },
                    ]);
                },
            ])->whereHas('provider')
            ->whereHas('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(self::CHECKED_PER_PAGE);
    }

    public function getActiveRequestsForPatient(int $patientId): Collection
    {
        return PatientRemovalRequest::query()
            ->where('patient_id', $patientId)
            ->where('provider_id', auth()->user()->provider_id)
            ->new()
            ->get();
    }

    public function send(array $data): PatientRemovalRequest
    {
        $removeRequest = PatientRemovalRequest::create([
            'provider_id' => auth()->user()->provider_id,
            'patient_id' => $data['patient_id'],
            'reason' => $data['reason'],
        ]);

        $comment = trans('comments.removal_request_has_been_sent', [
            'provider_name' => auth()->user()->provider->provider_name,
            'reason' => $data['reason'],
        ]);
        event(new NeedsWriteSystemComment($data['patient_id'], $comment));

        return $removeRequest;
    }

    public function accept(array $data): void
    {
        $account = Option::OA_ACCOUNT_1;
        $officeAllyHelper = new OfficeAllyHelper($account);
        $removeRequest = PatientRemovalRequest::find($data['request_id']);

        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        if ($removeRequest->patient->patient_id) {
            try {
                $officeAllyHelper->deleteUpcomingAppointments($removeRequest->patient->patient_id, $removeRequest->provider->officeally_id);
            } catch (OfficeallyAuthenticationException $e) {
                $job = (new RetryDeleteUpcomingAppointments($removeRequest->patient->id, $account, $removeRequest->provider->officeally_id))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryDeleteUpcomingAppointments($removeRequest->patient->id, $account, $removeRequest->provider->officeally_id))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }
        /**
         * unassign primary care provider
         */
        $dataForUpdate = [
            'new_primary_care_provider' => null,
            'delete_primary_care_provider' => $removeRequest->provider->officeally_id,
        ];

        RetryJobQueueHelper::dispatchRetryUpdatePatient($account, $dataForUpdate, $removeRequest->patient->id);

        $patientHasProvider = PatientHasProvider::where('providers_id', $removeRequest->provider_id)
                                ->where('patients_id', $removeRequest->patient_id)
                                ->first();
                                
        if ($patientHasProvider) {
            $patientHasProvider->delete();
        }

        $removeRequest->update([
            'approved_at' => Carbon::now(),
            'approver_id' => auth()->id(),
            'status' => PatientRemovalRequest::STATUS_ACCEPTED,
        ]);
        
        $comment = trans('comments.removal_request_has_been_approved', [
            'approved_by' => auth()->user()->getFullname(),
            'provider_name' => $removeRequest->provider->provider_name,
        ]);
        event(new NeedsWriteSystemComment($removeRequest->patient_id, $comment));
    }

    public function decline(array $data): void
    {
        $removeRequest = PatientRemovalRequest::find($data['request_id']);

        $removeRequest->update([
            'approved_at' => Carbon::now(),
            'approver_id' => auth()->id(),
            'status' => PatientRemovalRequest::STATUS_DECLINED,
            'approver_comment' => $data['reason'],
        ]);

        $comment = trans('comments.removal_request_has_been_declined', [
            'declined_by' => auth()->user()->getFullname(),
            'provider_name' => $removeRequest->provider->provider_name,
            'reason' => $data['reason'],
        ]);
        event(new NeedsWriteSystemComment($removeRequest->patient_id, $comment));
    }

    public function cancel(array $data): void
    {
        $removalRequestsBuilder = PatientRemovalRequest::query()
            ->new()
            ->where('provider_id',auth()->user()->provider_id)
            ->where('patient_id', $data['patient_id']);
        $removalRequests = clone $removalRequestsBuilder;
        $removalRequests = $removalRequests->get();
        $reason = $data['reason'];
        $removalRequests->each(function (PatientRemovalRequest $removalRequest) use ($reason) {
            $removalRequest->update([
                'approved_at' => Carbon::now(),
                'status' => PatientRemovalRequest::STATUS_CANCELED_BY_THERAPIST,
                'approver_comment' => $reason,
            ]);
        });

        if($removalRequests->count()) {
            $providerName = $removalRequests[0]->provider->provider_name;

            $comment = trans('comments.removal_request_has_been_canceled_by_therapist', [
                'canceled_by' => $providerName,
                'reason' => $data['reason'],
            ]);
            event(new NeedsWriteSystemComment($data['patient_id'], $comment));
        }
        event(new RemovalRequestListUpdated());
    }
}