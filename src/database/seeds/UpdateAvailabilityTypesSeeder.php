<?php

use App\Availability;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Collection;

class UpdateAvailabilityTypesSeeder extends Seeder
{
    public function run()
    {
        $dispatcher = Availability::getEventDispatcher();
        Availability::unsetEventDispatcher();

        Availability::chunkById('1000', function (Collection $availabilities) {
            $availabilities->each(function (Availability $availability) {
                $availability->update(['availability_type_id' => 1]);
            });
        });

        Availability::setEventDispatcher($dispatcher);
    }
}
