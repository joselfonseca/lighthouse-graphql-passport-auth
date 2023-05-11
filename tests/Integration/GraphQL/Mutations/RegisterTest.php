<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Joselfonseca\LighthouseGraphQLPassport\Notifications\VerifyEmail;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\UserVerifyEmail;

class RegisterTest extends TestCase
{
    public function test_it_registers_a_user()
    {
        Notification::fake();
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

    public function test_it_sends_email_verification()
    {
        config()->set('auth.providers.users.model', UserVerifyEmail::class);
        Notification::fake();
        Event::fake([Registered::class]);
        $this->createClient();
        $response = $this->postGraphQL([
            'query' => 'mutation {
                register(input: {
                    name: "My Name",
                    email: "jose@example.com",
                    password: "123456789qq",
                    password_confirmation: "123456789qq"
                }) {
                    status
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('register', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['register']);
        $this->assertEquals('MUST_VERIFY_EMAIL', $responseBody['data']['register']['status']);
        $user = UserVerifyEmail::first();
        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
        Event::assertDispatched(Registered::class);
    }

    public function test_it_does_not_send_an_email_verification_when_using_a_custom_implementation()
    {
        config()->set('auth.providers.users.model', UserVerifyEmail::class);
        config()->set('lighthouse-graphql-passport.verify_email.use_custom_implementation', true);
        Notification::fake();
        Event::fake([Registered::class]);
        $this->createClient();
        $response = $this->postGraphQL([
            'query' => 'mutation {
                register(input: {
                    name: "My Name",
                    email: "jose@example.com",
                    password: "123456789qq",
                    password_confirmation: "123456789qq"
                }) {
                    status
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('register', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['register']);
        $this->assertEquals('MUST_VERIFY_EMAIL', $responseBody['data']['register']['status']);
        $user = UserVerifyEmail::first();
        Notification::assertNotSentTo(
            [$user],
            VerifyEmail::class
        );
        Event::assertDispatched(Registered::class);
    }
}
