<?php

namespace App\Repositories\Appointment\Model;

use App\Appointment;
use App\Events\NeedsWriteSystemComment;
use App\Exceptions\EmptyGoogleAccountException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\PatientHasProvider;
use App\Models\GoogleMeeting;
use App\Office;
use App\OfficeRoom;
use App\Option;
use App\DTO\OfficeAlly\AppointmentResource;
use App\Enums\VisitType;
use App\Helpers\Sites\OfficeAlly\Enums\AppointmentStatuses;
use App\Patient;
use App\PatientStatus;
use App\Provider;
use App\Status;
use App\Traits\Appointments\GoogleCalendar;
use App\Traits\Appointments\SendProviderNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TelehealthAppointmentRepository implements TelehealthAppointmentRepositoryInterface
{
    /**
     * @param Patient       $patient
     * @param Provider|null $provider
     * @param Carbon|null   $startDate
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|mixed
     */
    public function getBaseQuery(Patient $patient, ?Provider $provider = null, ?Carbon $startDate = null)
    {
        return $patient->appointments()
            ->select(['appointments.*'])
            ->leftJoin('treatment_modalities', 'treatment_modalities.id', '=', 'appointments.treatment_modality_id')
            ->when($provider, function($query, $provider) {
                $query->where('providers_id', $provider->getKey());
            })
            ->when($startDate, function($query, $startDate) {
                $query->where('time', '>=',  $startDate->timestamp);
            })
            ->where('treatment_modalities.is_telehealth', true);
    }

    /**
     * @param Patient $patient
     * @param Provider|null $provider
     * @return Collection
     */
    public function upcomingByPatient(Patient $patient, ?Provider $provider): Collection
    {
        return $this->getBaseQuery($patient, $provider, Carbon::today())
            ->with(['googleMeet' => function($query) {
                $query
                    ->with('invitations')
                    ->orderBy('created_at', 'desc');
            }])
            ->onlyActive()
            ->get();
    }
}