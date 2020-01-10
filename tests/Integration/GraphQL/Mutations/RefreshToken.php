<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;

class RefreshToken extends TestCase
{
    public function test_it_refresh_a_token()
    {
        $this->createClient();
        User::create([
            'name'     => 'Jose Fonseca',
            'email'    => 'jose@example.com',
            'password' => bcrypt('123456789qq'),
        ]);
        $response = $this->postGraphQL([
            'query' => 'mutation {
                login(input: {
                    username: "jose@example.com",
                    password: "123456789qq"
                }) {
                    access_token
                    refresh_token
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $responseRefreshed = $this->postGraphQL([
            'query' => 'mutation {
                refreshToken(input: {
                    refresh_token: "'.$responseBody['data']['login']['refresh_token'].'"
                }) {
                    access_token
                    refresh_token
                }
            }',
        ]);
        $responseBodyRefreshed = json_decode($responseRefreshed->getContent(), true);
        $this->assertNotEquals($responseBody['data']['login']['access_token'], $responseBodyRefreshed['data']['refreshToken']['access_token']);
    }
}
