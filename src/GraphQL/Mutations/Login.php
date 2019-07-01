<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Login extends BaseAuthResolver
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
        $response = $this->makeRequest($credentials);
        $model = app(config('auth.providers.users.model'));
        $user = $model->where(config('lighthouse-graphql-passport.username'), $args['data']['username'])->firstOrFail();
        $response['user'] = $user;
        return $response;
    }

}
