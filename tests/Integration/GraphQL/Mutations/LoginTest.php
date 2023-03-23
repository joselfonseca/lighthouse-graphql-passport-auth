<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Facades\Event;
use Joselfonseca\LighthouseGraphQLPassport\Events\UserLoggedIn;
use Joselfonseca\LighthouseGraphQLPassport\Tests\Admin;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class LoginTest extends TestCase
{
    use MakesGraphQLRequests;

    public static function dataProvider(): array
    {
        return [
            'default'                    => [
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

    /**
     * @dataProvider dataProvider
     */
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

    /**
     * @dataProvider dataProvider
     */
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

    /**
     * @dataProvider dataProvider
     */
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

        self::assertSame('invalid_client', $response->json('errors.0.message'));
        self::assertSame('Client authentication failed', $response->json('errors.0.extensions.reason'));

        Event::assertNotDispatched(UserLoggedIn::class);
    }
}
