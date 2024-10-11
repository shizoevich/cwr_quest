<?php

use App\PatientVisit;
use App\VisitReason;
use Illuminate\Database\Seeder;

class UpdatePatientVisitsIsTelehealthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $telehealthReasonId = VisitReason::getTelehealthId();
        PatientVisit::where('reason_id', $telehealthReasonId)->update(['is_telehealth' => true]);
    }
}
