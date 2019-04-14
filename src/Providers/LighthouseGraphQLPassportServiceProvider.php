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
    }
}