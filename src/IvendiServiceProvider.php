<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use NorthBees\IvendiApi\Http\HttpTransport;

/**
 * This is the service provider for the iVendi API package.
 */
class IvendiServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ivendi.php' => config_path('ivendi.php'),
            ], 'ivendi.config');
        }
    }

    /**
     * Register any package services.
     *
     * Scoped rather than singleton so queue workers and Octane never share a
     * client across requests or tenants.
     */
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ivendi.php', 'ivendi');

        $this->app->scoped(Ivendi::class, function (Application $app): Ivendi {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('ivendi', []);

            return new Ivendi(new HttpTransport($config), $config);
        });

        $this->app->alias(Ivendi::class, 'ivendi');
    }
}
