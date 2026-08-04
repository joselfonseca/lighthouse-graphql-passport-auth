<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Integration\Passport13;

use Illuminate\Support\Facades\Auth;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;

/**
 * In Passport 13 an authenticated user's token() no longer returns the Token
 * model; it returns a Laravel\Passport\AccessToken value object built from the
 * request attributes. The package's Logout mutation calls token()->revoke(),
 * so these tests pin down that the value object still revokes the underlying
 * row.
 */
class AccessTokenTest extends TestCase
{
    private function authenticate(array $scopes = ['*']): array
    {
        $user = factory(User::class)->create();
        $this->createClientPersonal($user);

        return [$user, $user->createToken('test Token', $scopes)->accessToken];
    }

    public function test_the_authenticated_token_is_an_access_token_value_object()
    {
        [, $token] = $this->authenticate();

        $this->postGraphQL(['query' => '{ me { id } }'], [
            'Authorization' => 'Bearer '.$token,
        ]);

        self::assertInstanceOf(AccessToken::class, Auth::guard('api')->user()->token());
    }

    public function test_logout_revokes_the_underlying_token_row()
    {
        [, $token] = $this->authenticate();

        $this->postGraphQL(['query' => 'mutation { logout { status message } }'], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $tokenId = Auth::guard('api')->user()->token()->oauth_access_token_id;

        self::assertNotNull($tokenId);
        self::assertTrue(Passport::token()->newQuery()->whereKey($tokenId)->first()->revoked);
    }

    public function test_a_revoked_token_no_longer_authenticates()
    {
        [, $token] = $this->authenticate();

        $this->postGraphQL(['query' => 'mutation { logout { status } }'], [
            'Authorization' => 'Bearer '.$token,
        ]);

        // The guard memoises the resolved user for the lifetime of the
        // container, so drop it to force the next request to re-authenticate.
        $this->app['auth']->forgetGuards();

        $response = $this->postGraphQL(['query' => '{ me { id } }'], [
            'Authorization' => 'Bearer '.$token,
        ]);

        self::assertNull($response->json('data.me'));
    }

    public function test_the_access_token_resolves_scopes()
    {
        [, $token] = $this->authenticate(['*']);

        $this->postGraphQL(['query' => '{ me { id } }'], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $accessToken = Auth::guard('api')->user()->token();

        self::assertTrue($accessToken->can('anything'));
        self::assertFalse($accessToken->cant('anything'));
        self::assertFalse($accessToken->transient());
    }

    public function test_an_access_token_without_scopes_authorises_nothing()
    {
        [, $token] = $this->authenticate([]);

        $this->postGraphQL(['query' => '{ me { id } }'], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $accessToken = Auth::guard('api')->user()->token();

        self::assertFalse($accessToken->can('anything'));
        self::assertTrue($accessToken->cant('anything'));
    }
}
