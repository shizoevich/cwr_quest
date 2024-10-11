<?php

namespace App\Repositories\Statistics\Traits;

use Illuminate\Support\Facades\DB;
use App\Appointment;
use App\Provider;
use Carbon\Carbon;

trait PatientsStatistics
{
    protected function getProviderAppointmentsQuery($providerId, $startDate = null, $endDate = null)
    {
        return Appointment::query()
            ->select(['appointments.patients_id', 'appointments.created_at', DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->where('appointments.providers_id', '=', $providerId)
            ->where('patients.is_test', '=', 0)
            ->when(isset($startDate), function($query) use (&$startDate) {
                $query->havingRaw("appt_date >= DATE('{$startDate->toDateString()}')");
            })
            ->when(isset($endDate), function($query) use (&$endDate) {
                $query->havingRaw("appt_date <= DATE('{$endDate->toDateString()}')");
            });
    }

    public function getProviderNewPatientsCount($providerId, $startDate, $endDate)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        $appointmentsBeforePeriod = $this->getProviderAppointmentsQuery($providerId, $startDate->copy()->subYears(2), $startDate->copy()->subDay())->get();
        $appointmentsDuringPeriod = $this->getProviderAppointmentsQuery($providerId, $startDate, $endDate)->get();

        $patientsBeforePeriod = $appointmentsBeforePeriod->pluck('patients_id')->unique()->values()->all();
        $patientsDuringPeriod = $appointmentsDuringPeriod->pluck('patients_id')->unique()->values()->all();
        
        return count(array_diff($patientsDuringPeriod, $patientsBeforePeriod));
    }

    public function getNewPatientsStatisticsMapping($startDate, $endDate)
    {
        $providers = Provider::notTest()->select('id')->get();
        $newPatientsStatisticsMapping = [];

        foreach($providers as $provider) {
            $newPatientsStatisticsMapping[$provider->id] = $this->getProviderNewPatientsCount($provider->id, $startDate, $endDate);
        }

        return $newPatientsStatisticsMapping;
    }

    protected function getTransferredAppointment($providerId, $patientId, $createdAt, $startDate, $endDate)
    {
        return Appointment::query()
            ->select([DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->where('appointments.providers_id', '!=', $providerId)
            ->where('appointments.patients_id', '=', $patientId)
            ->where('appointments.created_at', '>', $createdAt)
            ->where('patients.is_test', '=', 0)
            ->havingRaw("appt_date >= DATE('{$startDate->toDateString()}')")
            ->havingRaw("appt_date <= DATE('{$endDate->toDateString()}')")
            ->first();
    }

    public function getProviderTransferredPatientsCount($providerId, $startDate, $endDate)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        $appointments = $this->getProviderAppointmentsQuery($providerId, $startDate->copy()->subMonth(), $endDate)
            ->withTrashed()
            ->orderBy('appointments.created_at')
            ->get();
        $appointmentsMapping = $appointments->reduce(function ($carry, $item) {
            $carry[$item->patients_id] = $item->created_at;
            return $carry;
        }, []);

        $transferredPatientsCount = 0;
        foreach ($appointmentsMapping as $patientId => $createdAt) {
            $transferredAppointment = $this->getTransferredAppointment($providerId, $patientId, $createdAt, $startDate, $endDate);
            if (isset($transferredAppointment)) {
                $transferredPatientsCount += 1;
            }
        }
        
        return $transferredPatientsCount;
    }

    public function getTransferredPatientsStatisticsMapping($startDate, $endDate)
    {
        $providers = Provider::notTest()->select('id')->get();
        $transferredPatientsStatisticsMapping = [];

        foreach($providers as $provider) {
            $transferredPatientsStatisticsMapping[$provider->id] = $this->getProviderTransferredPatientsCount($provider->id, $startDate, $endDate);
        }

        return $transferredPatientsStatisticsMapping;
    }
}
