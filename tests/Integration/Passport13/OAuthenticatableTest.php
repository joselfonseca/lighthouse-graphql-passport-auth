<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\Passport13;

use Joselfonseca\LighthouseGraphQLPassport\Tests\Admin;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Joselfonseca\LighthouseGraphQLPassport\Tests\UserVerifyEmail;
use Laravel\Passport\Contracts\OAuthenticatable;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Passport 13 requires authenticatable models to implement OAuthenticatable
 * rather than merely using the HasApiTokens trait. Applications using the
 * package must make the same change, so keep the shipped models honest.
 */
class OAuthenticatableTest extends TestCase
{
    public static function modelProvider(): array
    {
        return [
            'user' => [User::class],
            'admin' => [Admin::class],
            'user with email verification' => [UserVerifyEmail::class],
        ];
    }

    #[DataProvider('modelProvider')]
    public function test_the_auth_model_implements_the_passport_contract(string $modelClass)
    {
        self::assertInstanceOf(OAuthenticatable::class, new $modelClass());
    }

    public function test_the_auth_model_exposes_the_contract_methods()
    {
        $this->artisan('migrate');
        $user = factory(User::class)->create();

        self::assertSame('users', $user->getProviderName());
        self::assertCount(0, $user->tokens);
        self::assertCount(0, $user->oauthApps);
        self::assertNull($user->currentAccessToken());
        self::assertFalse($user->tokenCan('anything'));
        self::assertTrue($user->tokenCant('anything'));
    }
}
