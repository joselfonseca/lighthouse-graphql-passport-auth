<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Joselfonseca\LighthouseGraphQLPassport\Events\UserRefreshedToken;
use Laravel\Passport\Passport;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class RefreshToken.
 */
class RefreshToken extends BaseAuthResolver
{
    /**
     * @param $rootValue
     * @param array                                                    $args
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext|null $context
     * @param \GraphQL\Type\Definition\ResolveInfo                     $resolveInfo
     *
     * @throws \Exception
     *
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $credentials = $this->buildCredentials($args, 'refresh_token');

        $response = $this->makeRequest($credentials);

        // let's get the user id from the new Access token so we can emit an event
        $userId = $this->parseToken($response['access_token']);

        $model = $this->makeAuthModelInstance();

        $user = $model->findOrFail($userId);

        event(new UserRefreshedToken($user));

        return $response;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeAuthModelInstance()
    {
        return $this->getAuthModelFactory()->make();
    }

    /**
     * @param $accessToken
     *
     * @return false|mixed
     */
    public function parseToken($accessToken)
    {
        $public_key_path = Passport::keyPath('oauth-public.key');
        $private_key_path = Passport::keyPath('oauth-public.key');

        $config = Configuration::forAsymmetricSigner(new Sha256(), LocalFileReference::file($private_key_path), InMemory::file($public_key_path));

        $token = $config->parser()->parse((string) $accessToken);

        $claims = $token->claims();

        return $claims->get('sub');
    }
}
