<?php

use App\Office;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Office::class, static function (\Faker\Generator $faker) {
    return [
        'external_id' => $faker->unique()->randomNumber(),
        'office' => $faker->unique()->word,
        'tridiuum_is_enabled' => $faker->randomElement([true, false]),
    ];
});