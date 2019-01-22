<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\GraphQL\Mutations;

use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;

class Logout extends TestCase
{
    function test_it_invalidates_token_on_logout()
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
        $token = $responseBody['data']['login']['access_token'];
        $response = $this->postGraphQL([
            'query' => 'mutation {
                logout {
                    status
                    message
                }
            }'
        ], [
            'Authorization' => 'Bearer '.$token
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('logout', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['logout']);
        $this->assertArrayHasKey('message', $responseBody['data']['logout']);
    }
}