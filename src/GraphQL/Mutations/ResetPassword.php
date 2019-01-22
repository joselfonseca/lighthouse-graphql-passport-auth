<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use Illuminate\Support\Facades\Hash;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Validation\ValidationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\ValidationException as GraphQLValidationException;

class ResetPassword
{
    use ResetsPasswords, ValidatesRequests;

    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        try {
            $this->validate($args['data'], $this->rules());
        } catch (ValidationException $e) {
            throw new GraphQLValidationException($e->errors(), "Input validation failed");
        }

        $response = $this->broker()->reset($args['data'], function ($user, $password) {
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
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }

    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return mixed
     */
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        return $this->getValidationFactory()->make($data, $rules, $messages, $customAttributes)->validate();
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