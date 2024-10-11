<?php

use App\Models\UpcomingReauthorizationRequest;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpcomingReauthorizationRequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        Patient::query()
            ->select(['patients.id', 'patients.eff_start_date', 'patients.eff_stop_date'])
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patient_insurances_plans', 'patients.insurance_plan_id', '=', 'patient_insurances_plans.id')
            ->whereNotNull('patients.insurance_plan_id')
            ->where('patient_insurances_plans.is_verification_required', true)
            ->havingRaw(DB::raw("DATEDIFF(patients.eff_stop_date, '" . Carbon::now() . "')") . config('app.eff_stop_date_depth'))
            ->each(function($patient) use ($now) {
                UpcomingReauthorizationRequest::create([
                    'patient_id' => $patient->id,
                    'episode_start_date' => $patient->eff_start_date ?? $now,
                ]);
            });

    }
}
