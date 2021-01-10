<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Facades\Event;
use Joselfonseca\LighthouseGraphQLPassport\Events\UserLoggedIn;
use Joselfonseca\LighthouseGraphQLPassport\Tests\Admin;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class Login extends TestCase
{
    use MakesGraphQLRequests;

    public function dataProvider(): array
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
                'input' => [
                    'username' => 'something',
                    'password' => 'somethingelse',
                ],
            ]
        );

        $response->assertJsonStructure([
            'errors' => [
                [
                    'message',
                    'extensions' => [
                        'category',
                        'reason',
                    ],
                ],
            ],
        ]);

        $decodedResponse = json_decode($response->getContent(), 'true');

        $this->assertEquals('Incorrect username or password', $decodedResponse['errors'][0]['extensions']['reason']);

        Event::assertNotDispatched(UserLoggedIn::class);
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_it_returns_correct_error_for_client(string $modelClass, array $credentials, bool $hasFindForPassportMethod = false)
    {
        $this->artisan('migrate', ['--database' => 'testbench']);

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

        $response->assertJsonStructure([
            'errors' => [
                [
                    'message',
                    'extensions' => [
                        'category',
                        'reason',
                    ],
                ],
            ],
        ]);

        $decodedResponse = json_decode($response->getContent(), 'true');

        $this->assertEquals('invalid_client', $decodedResponse['errors'][0]['message']);

        Event::assertNotDispatched(UserLoggedIn::class);
    }
}
