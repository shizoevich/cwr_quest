<?php

use App\PatientDefaultComment;
use Illuminate\Database\Seeder;

class PatientDefaultCommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PatientDefaultComment::updateOrCreate([
            'name' => 'new_to_active'
        ], [
            'comment' => trans('patient_statuses.new_to_active'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'new_to_lost'
        ], [
            'comment' => trans('patient_statuses.new_to_lost'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'inactive_to_active'
        ], [
            'comment' => trans('patient_statuses.inactive_to_active'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'inactive_to_lost'
        ], [
            'comment' => trans('patient_statuses.inactive_to_lost'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'active_to_inactive'
        ], [
            'comment' => trans('patient_statuses.active_to_inactive'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'lost_to_new'
        ], [
            'comment' => trans('patient_statuses.lost_to_new'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'lost_to_active'
        ], [
            'comment' => trans('patient_statuses.lost_to_active'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'archived_to_active'
        ], [
            'comment' => trans('patient_statuses.archived_to_active'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'discharged_to_archived'
        ], [
            'comment' => trans('patient_statuses.discharged_to_archived'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'discharged_to_active'
        ], [
            'comment' => trans('patient_statuses.discharged_to_active'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'to_discharged'
        ], [
            'comment' => trans('patient_statuses.to_discharged'),
        ]);

        PatientDefaultComment::updateOrCreate([
            'name' => 'to_new'
        ], [
            'comment' => trans('patient_statuses.to_new'),
        ]);
    }
}
