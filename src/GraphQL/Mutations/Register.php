<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Register extends BaseAuthResolver
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
        $model = app(config('auth.providers.users.model'));
        $input = collect($args)->except('password_confirmation')->toArray();
        $input['password'] = bcrypt($input['password']);
        $model->fill($input);
        $model->save();
        $credentials = $this->buildCredentials([
            'username' => $args['email'],
            'password' => $args['password'],
        ]);
        $user = $model->where('email', $args['email'])->first();
        $response = $this->makeRequest($credentials);
        $response['user'] = $user;
        return $response;
    }

}
