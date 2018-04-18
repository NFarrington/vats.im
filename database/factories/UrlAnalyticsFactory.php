<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\UrlAnalytics::class, function (Faker $faker) {
    return [
        'user_id' => null,
        'url_id' => function () {
            return create(\App\Models\Url::class)->id;
        },
        'request_time' => mt_rand(),
        'http_host' => make(\App\Models\Domain::class)->url,
        'http_referer' => null,
        'http_user_agent' => $faker->userAgent,
        'remote_addr' => array_random([$faker->ipv4, $faker->ipv6]),
        'request_uri' => make(\App\Models\Url::class)->url,
        'get_data' => [],
        'custom_headers' => [],
        'response_code' => 302,
    ];
});