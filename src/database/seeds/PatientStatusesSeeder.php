<?php

use App\PatientStatus;
use Illuminate\Database\Seeder;

class PatientStatusesSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        PatientStatus::updateOrCreate([
            'status' => 'Active',
        ],[
            'status' => 'Active',
            'hex_color' => '02a756',
        ]);
        PatientStatus::updateOrCreate([
            'status' => 'Discharged',
        ], [
            'status' => 'Discharged',
            'hex_color' => '0000ff',
        ]);
        PatientStatus::updateOrCreate([
            'status' => 'Lost',
        ],[
            'status' => 'Lost',
            'hex_color' => 'fb0007',
        ]);
        PatientStatus::updateOrCreate([
            'status' => 'Inactive',
        ],[
            'status' => 'Inactive',
            'hex_color' => 'd09b00',
        ]);
        PatientStatus::updateOrCreate([
            'status' => 'Other',
        ],[
            'status' => 'Other',
        ]);
        PatientStatus::updateOrCreate([
            'status' => 'Archived',
        ],[
            'status' => 'Archived',
            'hex_color' => 'b1b1b1',
        ]);
        PatientStatus::updateOrCreate([
            'status' => 'New',
        ], [
            'status' => 'New',
        ]);

//        delete unused records
        PatientStatus::where('status', '=', 'Unassign')->delete();
    }
}
