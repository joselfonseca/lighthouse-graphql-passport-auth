<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;

class Register extends TestCase
{
    public function test_it_registers_a_user()
    {
        $this->createClient();
        $response = $this->postGraphQL([
            'query' => 'mutation {
                register(input: {
                    name: "My Name",
                    email: "jose@example.com",
                    password: "123456789qq",
                    password_confirmation: "123456789qq"
                }) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('register', $responseBody['data']);
        $this->assertArrayHasKey('access_token', $responseBody['data']['register']);
        $this->assertArrayHasKey('refresh_token', $responseBody['data']['register']);
        $this->assertArrayHasKey('user', $responseBody['data']['register']);
        $this->assertArrayHasKey('id', $responseBody['data']['register']['user']);
        $this->assertArrayHasKey('name', $responseBody['data']['register']['user']);
        $this->assertArrayHasKey('email', $responseBody['data']['register']['user']);
    }
}
