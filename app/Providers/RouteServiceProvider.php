<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/platform/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if ($forceScheme = config('app.force_scheme')) {
            $this->app['url']->forceScheme($forceScheme);
        }

        // overridden by App\Http\Middleware\SetRouteParameters::class
        $this->app['url']->defaults([
            'site_domain' => parse_url(config('app.url'), PHP_URL_HOST),
        ]);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->configureRateLimiting();

        $this->mapApiRoutes();

        $this->mapPlatformRoutes();

        $this->mapUrlRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapUrlRoutes()
    {
        Route::middleware('web-with-optional-sessions')
            ->namespace($this->namespace)
            ->group(base_path('routes/url.php'));
    }

    /**
     * Define the "platform" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapPlatformRoutes()
    {
        $platformAliases = config('app.url_aliases') ?: [];

        $primaryDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $secondaryDomains = is_string($platformAliases) ? explode(',', $platformAliases) : $platformAliases;
        $domains = array_merge([$primaryDomain], $secondaryDomains);

        $domain = trim(array_reduce($domains, function ($carry, $item) {
            return $carry.preg_quote($item).'|';
        }), '|');

        Route::pattern('site_domain', $domain);
        Route::domain('{site_domain}')
            ->middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/platform.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
