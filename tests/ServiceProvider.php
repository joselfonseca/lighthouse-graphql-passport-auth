<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../tests/migrations'));
    }

}