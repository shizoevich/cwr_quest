<?php

use App\Models\Officeally\OfficeallyTransactionType;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(OfficeallyTransactionType::class, static function (\Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});
