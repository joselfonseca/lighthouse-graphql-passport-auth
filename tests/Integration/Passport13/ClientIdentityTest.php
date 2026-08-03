<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\Passport13;

use Illuminate\Support\Str;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

/**
 * Passport 13 identifies clients by UUID and always hashes client secrets.
 *
 * Both changes reach into the package: the client id travels through its
 * configuration and back out through BaseAuthResolver::buildCredentials(), and
 * the secret it sends has to be the plain text one issued at creation time.
 */
class ClientIdentityTest extends TestCase
{
    use MakesGraphQLRequests;

    public function test_the_client_is_identified_by_a_uuid()
    {
        $client = $this->createClient();

        self::assertTrue(Str::isUuid($client->getKey()));
        self::assertSame($client->getKey(), config('lighthouse-graphql-passport.client_id'));
    }

    public function test_a_uuid_client_can_issue_tokens()
    {
        $this->createClient();
        factory(User::class)->create();

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation {
                login(input: {
                    username: "jose@example.com"
                    password: "123456789qq"
                }) {
                    access_token
                    refresh_token
                }
            }
        ');

        self::assertNotNull($response->json('data.login.access_token'));
        self::assertNull($response->json('errors'));
    }

    public function test_the_stored_client_secret_is_hashed()
    {
        $client = $this->createClient();

        self::assertNotNull($client->plainSecret);
        self::assertNotSame($client->plainSecret, $client->getAttributes()['secret']);
        self::assertTrue(app('hash')->check($client->plainSecret, $client->getAttributes()['secret']));
    }

    public function test_the_hashed_secret_is_not_accepted_as_a_client_secret()
    {
        $client = $this->createClient();
        config()->set('lighthouse-graphql-passport.client_secret', $client->getAttributes()['secret']);
        factory(User::class)->create();

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation {
                login(input: {
                    username: "jose@example.com"
                    password: "123456789qq"
                }) {
                    access_token
                }
            }
        ');

        self::assertSame('invalid_client', $response->json('errors.0.message'));
    }

    public function test_it_reads_the_auth_provider_from_the_client(): void
    {
        $this->createClient('admins');

        self::assertSame('admins', Client::find(config('lighthouse-graphql-passport.client_id'))->provider);
    }

    /**
     * Passport 13 removed the oauth_personal_access_clients table; personal
     * access clients are now simply clients carrying the personal_access grant.
     */
    public function test_it_creates_a_personal_access_client_without_the_legacy_table()
    {
        $this->artisan('migrate');
        self::assertFalse($this->app['db']->getSchemaBuilder()->hasTable('oauth_personal_access_clients'));

        $user = factory(User::class)->create();
        $client = $this->createClientPersonal($user);

        self::assertTrue($client->hasGrantType('personal_access'));
        self::assertNotNull($user->createToken('test token')->accessToken);
    }

    public function test_personal_access_clients_are_resolved_by_provider()
    {
        $this->artisan('migrate');
        $client = app(ClientRepository::class)->createPersonalAccessGrantClient('test', 'users');

        self::assertSame($client->getKey(), app(ClientRepository::class)->personalAccessClient('users')->getKey());
    }
}
