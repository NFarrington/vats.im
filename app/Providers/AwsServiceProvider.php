<?php

namespace App\Providers;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class AwsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DynamoDbClient::class, function ($app) {
            $config = config('services.dynamodb');

            $dynamoConfig = [
                'region' => $config['region'],
                'version' => 'latest',
                'endpoint' => $config['endpoint'] ?? null,
            ];

            if ($config['key'] && $config['secret']) {
                $dynamoConfig['credentials'] = Arr::only(
                    $config, ['key', 'secret', 'token']
                );
            }

            return new DynamoDbClient($dynamoConfig);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
