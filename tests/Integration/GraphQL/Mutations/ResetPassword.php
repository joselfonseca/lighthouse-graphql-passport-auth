<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;

/**
 * Class ResetPassword
 * @package Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\GraphQL\Mutations
 */
class ResetPassword extends TestCase
{
    function test_it_resets_a_password_for_user(): void
    {
        $this->createClient();
        $user = User::create([
            'name' => 'Jose Fonseca',
            'email' => 'jose@example.com',
            'password' => Hash::make('123456789qq')
        ]);

        $token = Password::createToken($user);

        $response = $this->postGraphQL([
            'query' => 'mutation UpdateForgottenPassword(
                    $email: String!
                    $token: String!
                    $password: String!
                    $confirmPassword: String!
                ) {
                updateForgottenPassword(input: {
                    email: $email
                    token: $token
                    password: $password
                    password_confirmation: $confirmPassword
                }) {
                    status
                    message
                }
            }',
            'variables' => [
                'email' => $user->email,
                'token' => $token,
                'password' => 'test1234',
                'confirmPassword' => 'test1234',
            ]
        ]);

        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('updateForgottenPassword', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['updateForgottenPassword']);
        $this->assertArrayHasKey('message', $responseBody['data']['updateForgottenPassword']);
        $this->assertEquals('PASSWORD_UPDATED', $responseBody['data']['updateForgottenPassword']['status']);
        $this->assertEquals('Your password has been reset!', $responseBody['data']['updateForgottenPassword']['message']);

        $user = User::find($user->id);
        $this->assertTrue(Hash::check('test1234', $user->password));
    }
}
