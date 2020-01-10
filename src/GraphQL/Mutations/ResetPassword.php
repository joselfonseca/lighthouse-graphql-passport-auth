<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class ResetPassword.
 */
class ResetPassword
{
    use ResetsPasswords;
    use ValidatesRequests;

    /**
     * @param $rootValue
     * @param array               $args
     * @param GraphQLContext|null $context
     * @param ResolveInfo         $resolveInfo
     *
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $args = collect($args)->except('directive')->toArray();
        $response = $this->broker()->reset($args, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        if ($response === Password::PASSWORD_RESET) {
            return [
                'status'  => 'PASSWORD_UPDATED',
                'message' => trans($response),
            ];
        }

        return [
            'status'  => 'PASSWORD_NOT_UPDATED',
            'message' => trans($response),
        ];
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string                                      $password
     *
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->save();

        event(new PasswordReset($user));
    }
}
