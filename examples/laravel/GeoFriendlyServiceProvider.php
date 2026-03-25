<?php

declare(strict_types=1);

namespace GeoFriendly\Laravel;

use GeoFriendly\GeoFriendly;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

/**
 * GeoFriendly Laravel Service Provider
 *
 * Integrates the geo-friendly package with Laravel framework
 */
class GeoFriendlyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/geofriendly.php',
            'geofriendly'
        );

        // Bind GeoFriendly singleton
        $this->app->singleton(GeoFriendly::class, function ($app) {
            return new GeoFriendly(config('geofriendly'));
        });

        // Alias for easier access
        $this->app->alias(GeoFriendly::class, 'geofriendly');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/config/geofriendly.php' => config_path('geofriendly.php'),
        ], 'geofriendly-config');

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\GeoGenerateCommand::class,
            ]);
        }

        // Schedule automatic generation
        if (config('geofriendly.auto_generate')) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('geo:generate')
                    ->cron(config('geofriendly.schedule', '0 2 * * *'))
                    ->description('Generate GEO-friendly files');
            });
        }

        // Listen for model events if auto_generate_on_change is enabled
        if (config('geofriendly.auto_generate_on_change')) {
            $this->registerModelObservers();
        }
    }

    /**
     * Register model observers for automatic regeneration
     */
    protected function registerModelObservers(): void
    {
        $models = config('geofriendly.observe_models', []);

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::saved(function ($instance) {
                    $this->app->make(GeoFriendly::class)->generate();
                });

                $model::deleted(function ($instance) {
                    $this->app->make(GeoFriendly::class)->generate();
                });
            }
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [GeoFriendly::class, 'geofriendly'];
    }
}
