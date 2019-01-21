<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use Illuminate\Http\Request;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Login
{
    /**
     * @param $rootValue
     * @param array $args
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext|null $context
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @return array
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $credentials = $this->buildCredentials($args);
        $request = Request::create('oauth/token', 'POST', $credentials,[], [], [
            'HTTP_Accept' => 'application/json'
        ]);
        $response = app()->handle($request);
        $tokens = json_decode($response->getContent(), true);
        return [
            // to keep backwards compatibility with existing clients
            'tokens' => [
                'accessToken' => $tokens['access_token'],
                'refreshToken' => $tokens['refresh_token'],
                'idToken' => "",
                'expiresIn' => $tokens['expires_in'],
                'tokenType' => $tokens['token_type'],

            ],
            // this will be the new format from 1.1 and so on
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in' => $tokens['expires_in'],
            'token_type' => $tokens['token_type']
        ];
    }

    /**
     * @param array $args
     * @return mixed
     */
    public function buildCredentials(array $args = [])
    {
        $credentials = collect($args)->get('data');
        $credentials['client_id'] = config('lighthouse-graphql-passport.client_id');
        $credentials['client_secret'] = config('lighthouse-graphql-passport.client_secret');
        $credentials['grant_type'] = 'password';
        return $credentials;
    }
}
