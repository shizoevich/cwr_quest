<?php

use App\PatientInsurance;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(PatientInsurance::class, static function (Faker\Generator $faker) {

    return [
        'external_id' => $faker->randomNumber($nbDigits = NULL, $strict = false),
        'insurance' => $faker->word,
        'address_line_1' => $faker->address,
        'city' => $faker->city,
        'state' => $faker->state,
        'zip' => $faker->postcode
    ];
});

