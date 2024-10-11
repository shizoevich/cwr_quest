<?php

namespace App\Repositories\Appointment\Model;

use App\Appointment;
use App\Patient;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface TelehealthAppointmentRepositoryInterface
{
    /**
     * @param Patient       $patient
     * @param Provider|null $provider
     *
     * @return Collection
     */
    public function upcomingByPatient(Patient $patient, ?Provider $provider): Collection;
    
    /**
     * @param Patient       $patient
     * @param Provider|null $provider
     * @param Carbon|null   $startDate
     *
     * @return mixed
     */
    public function getBaseQuery(Patient $patient, ?Provider $provider = null, ?Carbon $startDate = null);
}