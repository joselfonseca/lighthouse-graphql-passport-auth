<?php


namespace Joselfonseca\LighthouseGraphQLPassport\Providers;


use Illuminate\Support\ServiceProvider;
use Joselfonseca\LighthouseGraphQLPassport\Contracts\AuthModelFactory as AuthModelFactoryContract;
use Joselfonseca\LighthouseGraphQLPassport\Factories\AuthModelFactory;

class DependencyInjectionProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton(
            AuthModelFactoryContract::class,
            AuthModelFactory::class
        );

    }

}