<?php

use Illuminate\Database\Seeder;
use App\Training;
use App\Provider;

class SetTrainingsUserIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Training::all()->each(function(Training $training) {
            if (isset($training->provider_id)) {
                $provider = Provider::withTrashed()->where('id', $training->provider_id)->first();
                $training->user_id = optional($provider->user)->id;
                $training->save();
            }
        });
    }
}
