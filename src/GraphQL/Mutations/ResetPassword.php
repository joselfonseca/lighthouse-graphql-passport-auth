<?php
/**
 *
 */
namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use Illuminate\Support\Facades\Hash;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Class ResetPassword
 * @package Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations
 */
class ResetPassword
{
    use ResetsPasswords, ValidatesRequests;

    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $args = collect($args)->except('directive')->toArray();
        $response = $this->broker()->reset($args, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        if($response === Password::PASSWORD_RESET) {
            return [
                'status' => 'PASSWORD_UPDATED',
                'message' => trans($response)
            ];
        }

        return [
            'status' => 'PASSWORD_NOT_UPDATED',
            'message' => trans($response)
        ];
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->save();

        event(new PasswordReset($user));
    }
}
