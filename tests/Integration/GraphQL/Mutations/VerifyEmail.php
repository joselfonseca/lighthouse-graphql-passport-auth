<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\UserVerifyEmail;

class VerifyEmail extends TestCase
{
    public function test_it_verifies_email_with_token()
    {
        config()->set('auth.providers.users.model', UserVerifyEmail::class);
        Notification::fake();
        Event::fake([\Illuminate\Auth\Events\Verified::class]);
        $this->createClient();
        $user = UserVerifyEmail::create([
            'name'     => 'Jose Fonseca',
            'email'    => 'jose@example.com',
            'password' => bcrypt('123456789'),
        ]);
        $payload = base64_encode(json_encode([
            'id'         => $user->id,
            'hash'       => encrypt($user->getEmailForVerification()),
            'expiration' => encrypt(Carbon::now()->addMinutes(10)->toIso8601String()),
        ]));
        $response = $this->postGraphQL([
            'query' => 'mutation {
                verifyEmail(input: {
                    token: "'.$payload.'"
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
        $this->assertArrayHasKey('verifyEmail', $responseBody['data']);
        $this->assertArrayHasKey('access_token', $responseBody['data']['verifyEmail']);
        $this->assertArrayHasKey('refresh_token', $responseBody['data']['verifyEmail']);
        $this->assertArrayHasKey('user', $responseBody['data']['verifyEmail']);
        $this->assertArrayHasKey('id', $responseBody['data']['verifyEmail']['user']);
        $this->assertArrayHasKey('name', $responseBody['data']['verifyEmail']['user']);
        $this->assertArrayHasKey('email', $responseBody['data']['verifyEmail']['user']);
        $userUpdated = UserVerifyEmail::find($user->id);
        $this->assertNotNull($userUpdated->email_verified_at);
        Event::assertDispatched(\Illuminate\Auth\Events\Verified::class);
    }

    public function test_it_validates_token()
    {
        config()->set('auth.providers.users.model', UserVerifyEmail::class);
        Notification::fake();
        Event::fake([\Illuminate\Auth\Events\Verified::class]);
        $this->createClient();
        $user = UserVerifyEmail::create([
            'name'     => 'Jose Fonseca',
            'email'    => 'jose@example.com',
            'password' => bcrypt('123456789'),
        ]);
        $payload = base64_encode(json_encode([
            'id'         => $user->id,
            'hash'       => encrypt($user->getEmailForVerification()),
            'expiration' => encrypt(Carbon::now()->subMinutes(10)->toIso8601String()),
        ]));
        $response = $this->postGraphQL([
            'query' => 'mutation {
                verifyEmail(input: {
                    token: "'.$payload.'"
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
        $this->assertArrayHasKey('errors', $responseBody);
        $userUpdated = UserVerifyEmail::find($user->id);
        $this->assertNull($userUpdated->email_verified_at);
        Event::assertNotDispatched(\Illuminate\Auth\Events\Verified::class);
    }
}
