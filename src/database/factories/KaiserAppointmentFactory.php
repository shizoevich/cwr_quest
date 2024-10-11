<?php

use App\KaiserAppointment;
use App\Patient;
use App\Provider;
use App\TridiuumSite;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(KaiserAppointment::class, static function (\Faker\Generator $faker) {
    return [
        'internal_id' => $faker->unique()->uuid,
        'start_date' => $faker->dateTime,
        'duration' => 60,
        'reason' => 'Therapy Intake 60',
        'patient_id' => function () {
            return factory(Patient::class)->create()->id;
        },
        'mrn' => $faker->unique()->randomNumber(8),
        'first_name' => $faker->name,
        'last_name' => $faker->lastName,
        'sex' => $faker->randomElement(['M', 'F']),
        'cell_phone' => '000-000-0000',
        'date_of_birth' => Carbon::now()->subYears(rand(20, 55))->subDays(rand(0, 365))->format('Y-m-d'),
        'provider_id' => function () {
            return factory(Provider::class)->create()->id;
        },
        'status' => null,
        'user_id' => null,
        'site_id' => null,
        'is_virtual' => 1,
    ];
});