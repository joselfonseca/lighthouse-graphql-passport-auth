<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Providers;

use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Events\BuildingAST;

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
            BuildingAST::class,
            function (BuildingAST $buildingAST): string {
                return file_get_contents(__DIR__."/../../graphql/auth.graphql");
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
        $this->publishes(
            [
                __DIR__.'/../../config/config.php' => config_path('lighthouse-graphql-passport.php'),
            ]
        );
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'lighthouse-graphql-passport'
        );
    }
}