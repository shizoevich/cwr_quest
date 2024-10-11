<?php

use App\Appointment;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Appointment::class, function (Faker\Generator $faker) {
    
    return [
        'idAppointments' => $faker->randomNumber(),
        'resource' => 'Orange Grove',
        'time' => strtotime("+0 day"),  
        'is_initial' => $faker->randomElement([true, false]),
        'check_in' => $faker->text(35),
        'date_created' => Carbon::now()->format('M d, Y g:i A'),
        'visit_copay' => $faker->randomNumber(),
        'visit_length' => $faker->randomElement([30, 60]),
        'notes' => $faker->sentence,
        'custom_notes' => $faker->sentence,
        'reason_for_visit' => $faker->sentence,
        'sheldued_by' => 'CWR Billing',
        'patients_id' =>  function () {
            return factory(App\Patient::class)->create()->id;
        },
        'providers_id' => function () {
            return factory(App\Provider::class)->create()->id;
        },
        'offices_id' => function () {
            return factory(App\Office::class)->create()->id;
        },
        'appointment_statuses_id' => function () {
            return factory(App\Status::class)->create()->id; 
        },
        'is_paid' => $faker->randomElement([true, false]),
        'parsed_at' => Carbon::now()->format('M d, Y g:i A'),
        'not_found_count' => $faker->randomElement([true, false]),
        'payed' => $faker->randomElement([true, false]),
        'note_on_paper' => $faker->randomElement([true, false]),
        'start_completing_date' =>  Carbon::now()->format('M d, Y g:i A'),
        'start_creating_visit' =>  Carbon::now()->format('M d, Y g:i A'),
        'is_creating_visit_inprogress' => $faker->randomElement([true, false]),
        'is_created_by_tridiuum' => $faker->randomElement([true, false]),
    ];
});
