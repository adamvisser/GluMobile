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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

// We are not currently testing for uniqueness here as we would possibly want to run that test off of ACTUAL database entries instead of entries we are putting as tests.
$factory->define(App\Job::class, function (Faker\Generator $faker) {
    return [
        // Just going to use username as a string for faking text. otherwise things will get long
        'submitter_id' => $faker->randomNumber,
        'processor_id' => 0,
        'command' => 'echo "this is a seeded command"',
        'completed' => false,
        'priority' => $faker->randomNumber % 50,
    ];
});
