<?php

use Illuminate\Database\Seeder;
use App\TherapistSurvey;
use App\User;
use App\Provider;

class SetProvidersIsSupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TherapistSurvey::all()->each(function(TherapistSurvey $survey) {
            $user = User::withTrashed()->where('id', $survey->user_id)->first();
            if (!isset($user) || !isset($user->provider_id)) {
                return;
            }

            $provider = Provider::withTrashed()->where('id', $user->provider_id)->first();
            $provider->is_supervisor = $survey->is_supervisor;
            $provider->save();
        });
    }
}
