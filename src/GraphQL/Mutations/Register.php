<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Register extends BaseAuthResolver
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext|null  $context
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $model = $this->createAuthModel($args);

        $this->validateAuthModel($model);

        if ($model instanceof MustVerifyEmail) {
            $model->sendEmailVerificationNotification();

            event(new Registered($model));

            return [
                'tokens' => [],
                'status' => 'MUST_VERIFY_EMAIL',
            ];
        }
        $credentials = $this->buildCredentials([
            'username' => $args[config('lighthouse-graphql-passport.username')],
            'password' => $args['password'],
        ]);
        $user = $model->where(config('lighthouse-graphql-passport.username'), $args[config('lighthouse-graphql-passport.username')])->first();
        $response = $this->makeRequest($credentials);
        $response['user'] = $user;
        event(new Registered($user));

        return [
            'tokens' => $response,
            'status' => 'SUCCESS',
        ];
    }

    protected function validateAuthModel($model): void
    {
        $authModelClass = $this->getAuthModelFactory()->getClass();

        if ($model instanceof $authModelClass) {
            return;
        }

        throw new \RuntimeException("Auth model must be an instance of {$authModelClass}");
    }

    protected function createAuthModel(array $data): Model
    {
        $input = collect($data)->except('password_confirmation')->toArray();
        $input['password'] = Hash::make($input['password']);

        return $this->getAuthModelFactory()->create($input);
    }
}
