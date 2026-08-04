<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\Passport13;

use Illuminate\Http\Request;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

/**
 * league/oauth2-server 9 replaced the `message` property in OAuth error
 * responses with `error_description`. The package's BaseAuthResolver reads that
 * payload to decide which AuthenticationException to raise, so these tests
 * cover both the raw shape and the mapping the package builds on top of it.
 */
class OAuthErrorResponseTest extends TestCase
{
    use MakesGraphQLRequests;

    private function postToTokenEndpoint(array $credentials): array
    {
        $request = Request::create('oauth/token', 'POST', $credentials, [], [], [
            'HTTP_Accept' => 'application/json',
        ]);

        return json_decode(app()->handle($request)->getContent(), true);
    }

    public function test_the_token_endpoint_returns_error_description_and_no_message()
    {
        $this->createClient();
        factory(User::class)->create();

        $body = $this->postToTokenEndpoint([
            'grant_type' => 'password',
            'client_id' => config('lighthouse-graphql-passport.client_id'),
            'client_secret' => config('lighthouse-graphql-passport.client_secret'),
            'username' => 'jose@example.com',
            'password' => 'the-wrong-password',
        ]);

        self::assertSame('invalid_grant', $body['error']);
        self::assertSame('The user credentials were incorrect.', $body['error_description']);
        self::assertArrayNotHasKey('message', $body);
    }

    public function test_invalid_credentials_are_still_mapped_to_a_friendly_message()
    {
        $this->createClient();
        factory(User::class)->create();

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation {
                login(input: {
                    username: "jose@example.com"
                    password: "the-wrong-password"
                }) {
                    access_token
                }
            }
        ');

        self::assertSame('Authentication exception', $response->json('errors.0.message'));
        self::assertSame('Incorrect username or password', $response->json('errors.0.extensions.reason'));
    }

    public function test_an_invalid_refresh_token_is_mapped_to_a_friendly_message()
    {
        $this->createClient();
        factory(User::class)->create();

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation {
                refreshToken(input: {
                    refresh_token: "not-a-real-refresh-token"
                }) {
                    access_token
                }
            }
        ');

        self::assertSame('Authentication exception', $response->json('errors.0.message'));
        self::assertSame('Incorrect username or password', $response->json('errors.0.extensions.reason'));
    }

    /**
     * Errors that are not one of the mapped credential failures should keep
     * surfacing the server's own error_description rather than an empty string.
     */
    public function test_other_oauth_errors_surface_their_error_description()
    {
        $this->createClient();
        config()->set('lighthouse-graphql-passport.client_secret', 'not-the-right-secret');
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
        self::assertSame('Client authentication failed', $response->json('errors.0.extensions.reason'));
    }
}
