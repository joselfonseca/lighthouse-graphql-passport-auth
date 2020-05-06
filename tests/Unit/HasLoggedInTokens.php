<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Unit;

use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;

class HasLoggedInTokens extends TestCase
{
    public function test_it_gets_passport_tokens()
    {
        $this->createClient();
        $user = factory(User::class)->create();
        $this->actingAs($user);
        $tokens = $user->getTokens();
        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertArrayHasKey('expires_in', $tokens);
        $this->assertArrayHasKey('token_type', $tokens);
    }
}
