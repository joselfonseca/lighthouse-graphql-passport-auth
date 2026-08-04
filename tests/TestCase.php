<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Joselfonseca\LighthouseGraphQLPassport\Providers\LighthouseGraphQLPassportServiceProvider;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PassportServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withFactories(__DIR__.'/factories');
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            PassportServiceProvider::class,
            SocialiteServiceProvider::class,
            LighthouseServiceProvider::class,
            \Nuwave\Lighthouse\GlobalId\GlobalIdServiceProvider::class,
            \Nuwave\Lighthouse\OrderBy\OrderByServiceProvider::class,
            \Nuwave\Lighthouse\Pagination\PaginationServiceProvider::class,
            \Nuwave\Lighthouse\SoftDeletes\SoftDeletesServiceProvider::class,
            \Nuwave\Lighthouse\Validation\ValidationServiceProvider::class,
            LighthouseGraphQLPassportServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:gG84rusPbDk6AGOjbj5foirqMZm6tdD2fKZrbP0BS+A=');
        $app['config']->set('lighthouse.schema_path', __DIR__.'/schema.graphql');
        $app['config']->set('auth.guards', [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'api' => [
                'driver' => 'passport',
                'provider' => 'users',
            ],
        ]);
        $app['config']->set('auth.providers', [
            'users' => [
                'driver' => 'eloquent',
                'model' => User::class,
            ],
        ]);
    }

    public function test_assert_true()
    {
        $this->assertTrue(true);
    }

    /**
     * Create a passport client for testing.
     *
     * The client has to be confidential: the package always sends a
     * client_secret and Passport 13 refuses a secret against a public client.
     */
    public function createClient($provider = null)
    {
        $this->artisan('migrate');
        $client = app(ClientRepository::class)->createPasswordGrantClient('test', $provider, true);
        $this->useClient($client);

        return $client;
    }

    /**
     * Create a passport client for testing.
     */
    public function createClientPersonal($user)
    {
        $client = app(ClientRepository::class)->createPersonalAccessGrantClient('test');
        $this->useClient($client);

        return $client;
    }

    /**
     * Point the package configuration at the given client.
     *
     * Passport 13 always hashes client secrets, so the plain text secret is
     * available only on the model instance that issued it.
     */
    protected function useClient(Client $client)
    {
        config()->set('lighthouse-graphql-passport.client_id', $client->getKey());
        config()->set('lighthouse-graphql-passport.client_secret', $client->plainSecret);
    }

    /**
     * Execute a query as if it was sent as a request to the server.
     *
     * @param  mixed[]  $data
     * @param  mixed[]  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function postGraphQL(array $data, array $headers = [])
    {
        return $this->postJson('graphql', $data, $headers);
    }
}
