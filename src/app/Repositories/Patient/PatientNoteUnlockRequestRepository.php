<?php

namespace App\Repositories\Patient;

use App\Events\NeedsWriteSystemComment;
use App\Jobs\Comments\WriteCommentWithMention;
use App\Models\Patient\PatientNoteUnlockRequest;
use App\PatientNote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PatientNoteUnlockRequestRepository implements PatientNoteUnlockRequestRepositoryInterface
{
    const CHECKED_PER_PAGE = 15;

    public function getList(array $data = []): array
    {
        $data = PatientNoteUnlockRequest::query()
            ->select()
            ->addSelectStatusText()
            ->with([
                'patientNote:id,patients_id',
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
        return PatientNoteUnlockRequest::query()
            ->new()
            ->with([
                'patientNote:id,patients_id',
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
        return PatientNoteUnlockRequest::query()
            ->select([
                '*',
                DB::raw("IF(status = " . PatientNoteUnlockRequest::STATUS_ACCEPTED .
                    ", 'Approved', IF(status = " . PatientNoteUnlockRequest::STATUS_DECLINED . ", 'Declined', 'Canceled')) AS status_text"),
            ])
            ->with([
                'patientNote:id,patients_id',
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
            ])
            ->checked()
            ->whereHas('provider')
            ->whereHas('patientNote')
            ->orderBy('created_at', 'desc')
            ->paginate(self::CHECKED_PER_PAGE);
    }

    public function getActiveRequests(int $patientNoteId): Collection
    {
        return PatientNoteUnlockRequest::query()
            ->where('patient_note_id', $patientNoteId)
            ->where('provider_id', auth()->user()->provider_id)
            ->new()
            ->get();
    }

    public function send(array $data, PatientNote $patientNote): PatientNoteUnlockRequest
    {
        $unlockRequest = PatientNoteUnlockRequest::create([
            'provider_id' => auth()->user()->provider_id,
            'patient_note_id' => $patientNote->id,
            'reason' => $data['reason'],
        ]);

        $comment = trans('comments.patient_note_unlock_request_has_been_sent', [
            'provider_name' => auth()->user()->provider->provider_name,
            'reason' => $data['reason'],
            'date_of_service' => Carbon::parse($unlockRequest->patientNote->date_of_service)->format('m/d/Y'),
        ]);

        event(new NeedsWriteSystemComment($patientNote->patients_id, $comment));

        return $unlockRequest;
    }

    public function accept(array $data): void
    {
        $unlockRequest = PatientNoteUnlockRequest::query()
            ->with([
                'provider:id,provider_name',
                'provider.user:id,provider_id'
            ])
            ->where('id', $data['request_id'])
            ->first();
        $now = Carbon::now();

        $unlockRequest->update([
            'approved_at' => $now,
            'approver_id' => auth()->id(),
            'status' => PatientNoteUnlockRequest::STATUS_ACCEPTED,
        ]);

        $unlockRequest->patientNote->update([
            'start_editing_note_date' => $now,
        ]);

        $comment = trans('comments.patient_note_unlock_request_has_been_approved', [
            'approved_by' => auth()->user()->getFullname(),
            'provider_name' => $unlockRequest->provider->provider_name,
            'date_of_service' => Carbon::parse($unlockRequest->patientNote->date_of_service)->format('m/d/Y'),
        ]);

        dispatch(new WriteCommentWithMention(
            $comment,
            $unlockRequest->patientNote->patients_id,
            $unlockRequest->provider->user->id,
            auth()->id(),
            'PatientAlert'
        ));
    }

    public function decline(array $data): void
    {
        $unlockRequest = PatientNoteUnlockRequest::query()
            ->with([
                'provider:id,provider_name',
                'provider.user:id,provider_id'
            ])
            ->where('id', $data['request_id'])
            ->first();
        $unlockRequest->update([
            'approved_at' => Carbon::now(),
            'approver_id' => auth()->id(),
            'status' => PatientNoteUnlockRequest::STATUS_DECLINED,
            'approver_comment' => $data['reason'],
        ]);

        $comment = trans('comments.patient_note_unlock_request_has_been_declined', [
            'declined_by' => auth()->user()->getFullname(),
            'provider_name' => $unlockRequest->provider->provider_name,
            'reason' => $data['reason'],
            'date_of_service' => Carbon::parse($unlockRequest->patientNote->date_of_service)->format('m/d/Y'),
        ]);

        dispatch(new WriteCommentWithMention(
            $comment,
            $unlockRequest->patientNote->patients_id,
            $unlockRequest->provider->user->id,
            auth()->id(),
            'PatientAlert'
        ));
    }

    public function cancel(array $data): void
    {
        $unlockRequest = PatientNoteUnlockRequest::query()
            ->where('patient_note_id', $data['patient_note_id'])
            ->new()
            ->first();

        $unlockRequest->update([
            'approved_at' => Carbon::now(),
            'status' => PatientNoteUnlockRequest::STATUS_CANCELED_BY_THERAPIST,
            'approver_comment' => $data['reason'],
        ]);

        $comment = trans('comments.patient_note_unlock_request_has_been_canceled_by_therapist', [
            'canceled_by' => auth()->user()->provider->provider_name,
            'reason' => $data['reason'],
            'date_of_service' => Carbon::parse($unlockRequest->patientNote->date_of_service)->format('m/d/Y'),
        ]);

        event(new NeedsWriteSystemComment($unlockRequest->patientNote->patients_id, $comment));
    }
}