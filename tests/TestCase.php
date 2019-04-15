<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests;

use Laravel\Passport\Passport;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Joselfonseca\LighthouseGraphQLPassport\Providers\LighthouseGraphQLPassportServiceProvider;

class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            PassportServiceProvider::class,
            LighthouseServiceProvider::class,
            LighthouseGraphQLPassportServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:gG84rusPbDk6AGOjbj5foirqMZm6tdD2fKZrbP0BS+A=');
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('lighthouse.schema.register', __DIR__ . '/schema.graphql');
        $app['config']->set('auth.guards', [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'api' => [
                'driver' => 'passport',
                'provider' => 'users',
            ]
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
     * Create a passport client for testing
     */
    public function createClient()
    {
        $this->artisan('migrate', ['--database' => 'testbench']);
        Passport::loadKeysFrom(__DIR__ . '/storage/');
        $client = app(ClientRepository::class)->createPasswordGrantClient(null, 'test', 'http://localhost');
        config()->set('lighthouse-graphql-passport.client_id', $client->id);
        config()->set('lighthouse-graphql-passport.client_secret', $client->secret);
    }

    /**
     * Create a passport client for testing
     */
    public function createClientPersonal($user)
    {
        Passport::loadKeysFrom(__DIR__ . '/storage/');
        $client = app(ClientRepository::class)->createPersonalAccessClient($user->id, 'test', 'http://localhost');
        config()->set('lighthouse-graphql-passport.client_id', $client->id);
        config()->set('lighthouse-graphql-passport.client_secret', $client->secret);
    }


    /**
     * Execute a query as if it was sent as a request to the server.
     *
     * @param mixed[] $data
     * @param mixed[] $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function postGraphQL(array $data, array $headers = [])
    {
        return $this->postJson('graphql', $data, $headers);
    }

}