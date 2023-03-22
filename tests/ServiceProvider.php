<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests;

use Laravel\Passport\Passport;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../tests/migrations'));
        if (method_exists(Passport::class, 'routes')) {
            Passport::routes();
        }
        Passport::loadKeysFrom(__DIR__.'/storage/');
        config()->set('lighthouse.route.middleware', [
            \Nuwave\Lighthouse\Http\Middleware\AcceptJson::class,
            \Joselfonseca\LighthouseGraphQLPassport\Http\Middleware\AuthenticateWithApiGuard::class,
        ]);
    }
}
