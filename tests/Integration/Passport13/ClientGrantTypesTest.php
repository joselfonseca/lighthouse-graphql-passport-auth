<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\Passport13;

use Illuminate\Support\Facades\Auth;
use Joselfonseca\LighthouseGraphQLPassport\Models\SocialProvider;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Laravel\Passport\ClientRepository;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

/**
 * Passport 13 records the grant types a client accepts in its `grant_types`
 * column, and league/oauth2-server 9 refuses any grant that is not listed.
 *
 * That gate applies to every grant, including the two the package registers of
 * its own, so these tests pin down which clients may reach them.
 */
class ClientGrantTypesTest extends TestCase
{
    use MakesGraphQLRequests;

    public function test_a_password_grant_client_lists_the_password_grant()
    {
        $client = $this->createClient();

        self::assertTrue($client->hasGrantType('password'));
        self::assertFalse($client->hasGrantType('logged_in_grant'));
        self::assertFalse($client->hasGrantType('social_grant'));
    }

    public function test_a_client_without_the_password_grant_cannot_log_in()
    {
        $this->artisan('migrate');
        $client = app(ClientRepository::class)
            ->createAuthorizationCodeGrantClient('test', ['http://localhost'], true);
        config()->set('lighthouse-graphql-passport.client_id', $client->getKey());
        config()->set('lighthouse-graphql-passport.client_secret', $client->plainSecret);

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

        self::assertSame('unauthorized_client', $response->json('errors.0.message'));
        self::assertSame(
            'The authenticated client is not authorized to use this authorization grant type.',
            $response->json('errors.0.extensions.reason')
        );
    }

    /**
     * The package's grants are extensions of the password grant, so a password
     * grant client may use them even though `logged_in_grant` is never present
     * in the client's grant_types column.
     */
    public function test_a_password_grant_client_may_use_the_logged_in_grant()
    {
        $this->createClient();
        $user = factory(User::class)->create();
        Auth::login($user);

        $tokens = $user->getTokens();

        self::assertArrayNotHasKey('error', $tokens);
        self::assertArrayHasKey('access_token', $tokens);
        self::assertArrayHasKey('refresh_token', $tokens);
    }

    public function test_a_password_grant_client_may_use_the_social_grant()
    {
        $this->createClient();
        $provider = factory(SocialProvider::class)->create();

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($provider->provider_id)
            ->shouldReceive('getName')
            ->andReturn($provider->user->name)
            ->shouldReceive('getEmail')
            ->andReturn($provider->user->email);
        Socialite::shouldReceive('driver->userFromToken')->andReturn($abstractUser);

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation socialLogin($input: SocialLoginInput!) {
                socialLogin(input: $input) {
                    access_token
                }
            }
        ', [
            'input' => [
                'provider' => 'github',
                'token' => 'some-valid-token-from-github',
            ],
        ]);

        self::assertNull($response->json('errors'));
        self::assertNotNull($response->json('data.socialLogin.access_token'));
    }

    /**
     * A client that may not use the password grant must not reach the
     * package's own grants through the back door either.
     */
    public function test_a_client_without_the_password_grant_may_not_use_the_logged_in_grant()
    {
        $this->artisan('migrate');
        $client = app(ClientRepository::class)
            ->createAuthorizationCodeGrantClient('test', ['http://localhost'], true);
        config()->set('lighthouse-graphql-passport.client_id', $client->getKey());
        config()->set('lighthouse-graphql-passport.client_secret', $client->plainSecret);

        $user = factory(User::class)->create();
        Auth::login($user);

        $tokens = $user->getTokens();

        self::assertSame('unauthorized_client', $tokens['error']);
        self::assertArrayNotHasKey('access_token', $tokens);
    }
}
