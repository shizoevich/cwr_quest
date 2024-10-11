<?php

use App\Status;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Status::class, function (Faker\Generator $faker) {
    return [
        'external_id' => $faker->unique()->randomNumber(),
        'status' => $faker->unique()->word,
    ];
});