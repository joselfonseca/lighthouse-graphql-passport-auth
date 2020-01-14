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
                    tokens {
                        access_token
                        refresh_token
                        user {
                            id
                            name
                            email
                        }
                    }
                    status
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('register', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['register']);
        $this->assertArrayHasKey('tokens', $responseBody['data']['register']);
        $this->assertArrayHasKey('access_token', $responseBody['data']['register']['tokens']);
        $this->assertArrayHasKey('refresh_token', $responseBody['data']['register']['tokens']);
        $this->assertArrayHasKey('user', $responseBody['data']['register']['tokens']);
        $this->assertArrayHasKey('id', $responseBody['data']['register']['tokens']['user']);
        $this->assertArrayHasKey('name', $responseBody['data']['register']['tokens']['user']);
        $this->assertArrayHasKey('email', $responseBody['data']['register']['tokens']['user']);
        $this->assertEquals('SUCCESS', $responseBody['data']['register']['status']);
    }
}
