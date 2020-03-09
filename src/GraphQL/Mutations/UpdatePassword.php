<?php


namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Hash;
use Joselfonseca\LighthouseGraphQLPassport\Events\PasswordUpdated;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdatePassword
 * @package Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations
 */
class UpdatePassword
{
    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext|null $context
     * @param ResolveInfo $resolveInfo
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        $user->password = Hash::make($args['password']);
        $user->save();
        event(new PasswordUpdated($user));
        return [
            'status' => 'PASSWORD_UPDATED',
            'message' => _('Your password has been updated')
        ];
    }
}
