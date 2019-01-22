<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;

class LoginTest extends TestCase
{

    function test_it_gets_access_token()
    {
        $this->createClient();
        User::create([
            'name' => 'Jose Fonseca',
            'email' => 'jose@example.com',
            'password' => bcrypt('123456789qq')
        ]);
        $response = $this->postGraphQL([
            'query' => 'mutation {
                login(data: {
                    username: "jose@example.com",
                    password: "123456789qq"
                }) {
                    access_token
                    refresh_token
                }
            }'
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('login', $responseBody['data']);
        $this->assertArrayHasKey('access_token', $responseBody['data']['login']);
        $this->assertArrayHasKey('refresh_token', $responseBody['data']['login']);
    }

}