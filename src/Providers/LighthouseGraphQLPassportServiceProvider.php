<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Providers;

use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Events\BuildSchemaString;

/**
 * Class LighthouseGraphQLPassportServiceProvider
 *
 * @package Joselfonseca\LighthouseGraphQLPassport\Providers
 */
class LighthouseGraphQLPassportServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('lighthouse-graphql-passport.migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../../migrations');
        }
    }

    /**
     *
     */
    public function register()
    {
        $this->registerConfig();

        app('events')->listen(
            BuildSchemaString::class,
            function (): string {
                if (config('lighthouse-graphql-passport.schema')) {
                    return file_get_contents(config('lighthouse-graphql-passport.schema'));
                }

                return file_get_contents(__DIR__ . "/../../graphql/auth.graphql");
            }
        );
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php',
            'lighthouse-graphql-passport'
        );

        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('lighthouse-graphql-passport.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../graphql/auth.graphql' => base_path('graphql/auth.graphql'),
        ], 'schema');

        $this->publishes([
            __DIR__ . '/../../migrations/2019_11_19_000000_create_social_providers_table.php' => base_path('database/migrations/2019_11_19_000000_create_social_providers_table.php'),
        ], 'migrations');
    }
}
