<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Facades\Event;
use Joselfonseca\LighthouseGraphQLPassport\Events\UserLoggedIn;
use Joselfonseca\LighthouseGraphQLPassport\Tests\Admin;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\DataProvider;

class LoginTest extends TestCase
{
    use MakesGraphQLRequests;

    public static function dataProvider(): array
    {
        return [
            'default' => [
                User::class,
                [
                    'username' => 'jose@example.com',
                    'password' => '123456789qq',
                ],
            ],
            'findForPassport' => [
                Admin::class,
                [
                    'username' => 'Jose Fonseca',
                    'password' => '123456789qq',
                ],
                true,

            ],
        ];
    }

    #[DataProvider('dataProvider')]
    public function test_it_gets_access_token(string $modelClass, array $credentials, bool $hasFindForPassportMethod = false)
    {
        Event::fake([UserLoggedIn::class]);

        $this->app['config']->set('auth.providers.users.model', $modelClass);

        $this->createClient();

        $user = factory($modelClass)->create();

        if ($hasFindForPassportMethod) {
            self::assertTrue(method_exists($modelClass, 'findForPassport'));
        }

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation Login($input: LoginInput) {
                login(input: $input) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
            [
                'input' => $credentials,
            ]
        );

        $response->assertJsonStructure([
            'data' => [
                'login' => [
                    'access_token',
                    'refresh_token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ],
        ]);

        Event::assertDispatched(UserLoggedIn::class, function (UserLoggedIn $event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[DataProvider('dataProvider')]
    public function test_it_returns_error_for_invalid_credentials(string $modelClass, array $credentials, bool $hasFindForPassportMethod = false)
    {
        Event::fake([UserLoggedIn::class]);

        $this->app['config']->set('auth.providers.users.model', $modelClass);

        $this->createClient();

        factory($modelClass)->create();

        if ($hasFindForPassportMethod) {
            self::assertTrue(method_exists($modelClass, 'findForPassport'));
        }

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation {
                login(input: {
                    username: "something" 
                    password: "somethingelse"
                }) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ');

        self::assertSame('Authentication exception', $response->json('errors.0.message'));
        self::assertSame('Incorrect username or password', $response->json('errors.0.extensions.reason'));

        Event::assertNotDispatched(UserLoggedIn::class);
    }

    #[DataProvider('dataProvider')]
    public function test_it_returns_correct_error_for_client(string $modelClass, array $credentials, bool $hasFindForPassportMethod = false)
    {
        Event::fake([UserLoggedIn::class]);

        $this->app['config']->set('auth.providers.users.model', $modelClass);

        if ($hasFindForPassportMethod) {
            self::assertTrue(method_exists($modelClass, 'findForPassport'));
        }

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation Login($input: LoginInput) {
                login(input: $input) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
            [
                'input' => $credentials,
            ]
        );

        // No client is configured at all. league/oauth2-server 9 refuses the
        // missing client_id parameter before it ever looks a client up, so this
        // is an invalid_request rather than the invalid_client of earlier versions.
        self::assertSame('invalid_request', $response->json('errors.0.message'));
        self::assertSame(
            'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.',
            $response->json('errors.0.extensions.reason')
        );

        Event::assertNotDispatched(UserLoggedIn::class);
    }

    #[DataProvider('dataProvider')]
    public function test_it_returns_correct_error_for_invalid_client_secret(string $modelClass, array $credentials, bool $hasFindForPassportMethod = false)
    {
        Event::fake([UserLoggedIn::class]);

        $this->app['config']->set('auth.providers.users.model', $modelClass);

        $this->createClient();
        config()->set('lighthouse-graphql-passport.client_secret', 'not-the-right-secret');

        factory($modelClass)->create();

        if ($hasFindForPassportMethod) {
            self::assertTrue(method_exists($modelClass, 'findForPassport'));
        }

        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation Login($input: LoginInput) {
                login(input: $input) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
            [
                'input' => $credentials,
            ]
        );

        self::assertSame('invalid_client', $response->json('errors.0.message'));
        self::assertSame('Client authentication failed', $response->json('errors.0.extensions.reason'));

        Event::assertNotDispatched(UserLoggedIn::class);
    }
}
