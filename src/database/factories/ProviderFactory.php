<?php

use App\Provider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Provider::class, static function (Faker\Generator $faker) {
    return [
        'provider_name' => $faker->unique()->name,
        'officeally_id' => $faker->randomNumber(),
        'phone' => $faker->phoneNumber,
        'tridiuum_sync_availability' => 1,
        'tridiuum_sync_appointments' => 1,
    ];
});

$factory->state(Provider::class, 'trashed', static function (\Faker\Generator $faker) {
    return [
        'deleted_at' => Carbon::now(),
    ];
});
