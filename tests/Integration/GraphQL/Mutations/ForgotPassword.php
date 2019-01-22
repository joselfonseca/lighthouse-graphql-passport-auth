<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;

class ForgotPassword extends TestCase
{
    function test_it_sends_recover_password_email()
    {
        Mail::fake();
        Notification::fake();
        $this->createClient();
        $user = User::create([
            'name' => 'Jose Fonseca',
            'email' => 'jose@example.com',
            'password' => bcrypt('123456789qq')
        ]);
        $response = $this->postGraphQL([
            'query' => 'mutation {
                forgotPassword(data: {
                    email: "jose@example.com"
                }) {
                    status
                    message
                }
            }'
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('forgotPassword', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['forgotPassword']);
        $this->assertArrayHasKey('message', $responseBody['data']['forgotPassword']);
        $this->assertEquals('EMAIL_SENT', $responseBody['data']['forgotPassword']['status']);
        Notification::assertSentTo($user, ResetPassword::class);
    }
}