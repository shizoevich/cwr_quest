<?php

namespace App\Repositories\TreatmentModality;

use App\Models\TreatmentModality;
use App\Status;
use App\Appointment;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TreatmentModalityRepository implements TreatmentModalityRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        $treatmentModalityQuery = TreatmentModality::query();

        if (isset($filters['patient_id'])) {
            $visitExists = Appointment::query()
                ->where('patients_id', $filters['patient_id'])
                ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
                ->where('time', '>=', Carbon::today()->subMonths(config('app.treatment_episode_months'))->timestamp)
                ->when(isset($filters['appointment_id']), function ($query) use (&$filters) {
                    $query->where('id', '!=', $filters['appointment_id']);
                })
                ->exists();

            if (!$visitExists) {
                $appointment = null;
                if (isset($filters['appointment_id'])) {
                    $appointment = Appointment::find($filters['appointment_id']);
                }

                $initialEvaluationExists = Appointment::query()
                    ->where('patients_id', $filters['patient_id'])
                    ->where('is_initial', 1)
                    ->where('appointment_statuses_id', Status::getActiveId())
                    ->when(isset($appointment), function ($query) use (&$appointment) {
                        $query->where('time', '<', $appointment->time);
                    })
                    ->exists();

                if (!$initialEvaluationExists) {
                    $treatmentModalityQuery =  TreatmentModality::whereIn('id', TreatmentModality::initialEvaluationIds());
                }
            }
        }

        return $treatmentModalityQuery
            ->orderBy('order')
            ->get();
    }
}
