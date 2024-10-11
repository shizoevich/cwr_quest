<?php

use App\Patient;
use App\PatientStatus;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Patient::class, static function (Faker\Generator $faker) {
    $uniquePatientId = $faker->unique()->numberBetween(100000000, 999999999);
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'middle_initial' => $faker->randomLetter,
        'email' => $faker->safeEmail,
        'secondary_email' => $faker->email,
        'cell_phone' => $faker->phoneNumber,
        'status_id' => PatientStatus::inRandomOrder()->take(1)->first()->id,
        'patient_id' =>   str_pad($uniquePatientId, 9, '0', STR_PAD_LEFT),
        /*function () {
            return factory(App\PatientStatus::class)->create()->id;
        }*/
    ];
});

