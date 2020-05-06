<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Joselfonseca\LighthouseGraphQLPassport\Tests\Admin;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class LoginTest extends TestCase
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
        $this->app['config']->set('auth.providers.users.model', $modelClass);

        $this->createClient();

        factory($modelClass)->create();

        if ($hasFindForPassportMethod) {
            self::assertTrue(method_exists($modelClass, 'findForPassport'));
        }

        $response = $this->graphQL(/** @lang GraphQL */ '
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
    }
}
