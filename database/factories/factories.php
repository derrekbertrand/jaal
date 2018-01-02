<?php

use DialInno\Jaal\Tests\Models\Account;
use DialInno\Jaal\Tests\Models\Agent;
use DialInno\Jaal\Tests\Models\Contact;
use DialInno\Jaal\Tests\Models\Tag;

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
$factory = app(\Illuminate\Database\Eloquent\Factory::class);

$factory->define(Account::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->company,
        'website' => $faker->domainName,
    ];
});

$factory->define(Agent::class, function (Faker\Generator $faker) {
    static $password;
 
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'job_title' => $faker->jobTitle,
        'email' => $faker->unique()->email,
        'password' => $password ?: $password = bcrypt('secret'),
    ];
});

$factory->define(Contact::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'job_title' => $faker->jobTitle,
        'cell_phone' => $faker->e164PhoneNumber,
        'office_phone' => $faker->phoneNumber,
        'email' => $faker->email,
    ];
});

$factory->define(Tag::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word.' snarf',
    ];
});
