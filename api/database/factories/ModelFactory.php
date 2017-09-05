<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

function randDate()
{
    return \Carbon\Carbon::now()
        ->subDays(rand(1, 100))
        ->subHours(rand(1, 23))
        ->subMinutes(rand(1, 60));
}

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $createdAt = randDate();

    return [
        'real_name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => app('hash')->make(123456),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ];
});
