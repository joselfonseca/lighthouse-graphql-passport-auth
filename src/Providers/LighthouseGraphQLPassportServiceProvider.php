<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Providers;

use Illuminate\Support\ServiceProvider;
use Joselfonseca\LighthouseGraphQLPassport\OAuthGrants\LoggedInGrant;
use Joselfonseca\LighthouseGraphQLPassport\OAuthGrants\SocialGrant;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use Nuwave\Lighthouse\Events\BuildSchemaString;

/**
 * Class LighthouseGraphQLPassportServiceProvider.
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
        app(AuthorizationServer::class)->enableGrantType($this->makeCustomRequestGrant(), Passport::tokensExpireIn());
        app(AuthorizationServer::class)->enableGrantType($this->makeLoggedInRequestGrant(), Passport::tokensExpireIn());
    }

    public function register()
    {
        $this->registerConfig();

        app('events')->listen(
            BuildSchemaString::class,
            function (): string {
                if (config('lighthouse-graphql-passport.schema')) {
                    return file_get_contents(config('lighthouse-graphql-passport.schema'));
                }

                return file_get_contents(__DIR__.'/../../graphql/auth.graphql');
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
            __DIR__.'/../../config/config.php',
            'lighthouse-graphql-passport'
        );

        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('lighthouse-graphql-passport.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../../graphql/auth.graphql' => base_path('graphql/auth.graphql'),
        ], 'schema');

        $this->publishes([
            __DIR__.'/../../migrations/2019_11_19_000000_update_social_provider_users_table.php' => base_path('database/migrations/2019_11_19_000000_update_social_provider_users_table.php'),
        ], 'migrations');
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return SocialGrant
     */
    protected function makeCustomRequestGrant()
    {
        $grant = new SocialGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );
        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return LoggedInGrant
     */
    protected function makeLoggedInRequestGrant()
    {
        $grant = new LoggedInGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );
        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }
}
