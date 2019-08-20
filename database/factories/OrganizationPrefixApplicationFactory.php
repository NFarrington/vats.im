<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Organization;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(App\Models\OrganizationPrefixApplication::class, function (Faker $faker) {
    return [
        'organization_id' => function () {
            return create(Organization::class)->id;
        },
        'user_id' => function () {
            return create(User::class)->id;
        },
        'identity_url' => $faker->url,
        'prefix' => Str::random(3),
    ];
});
