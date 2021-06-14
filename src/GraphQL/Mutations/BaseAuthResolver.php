<?php

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use Illuminate\Http\Request;
use Joselfonseca\LighthouseGraphQLPassport\Contracts\AuthModelFactory;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\AuthenticationException;

/**
 * Class BaseAuthResolver.
 */
class BaseAuthResolver
{
    /**
     * @param array  $args
     * @param string $grantType
     *
     * @return mixed
     */
    public function buildCredentials(array $args = [], $grantType = 'password')
    {
        $args = collect($args);
        $credentials = $args->except('directive')->toArray();
        $credentials['client_id'] = $args->get('client_id', config('lighthouse-graphql-passport.client_id'));
        $credentials['client_secret'] = $args->get('client_secret', config('lighthouse-graphql-passport.client_secret'));
        $credentials['grant_type'] = $grantType;

        return $credentials;
    }

    /**
     * @param array $credentials
     *
     * @throws AuthenticationException
     *
     * @return mixed
     */
    public function makeRequest(array $credentials)
    {
        $request = Request::create('oauth/token', 'POST', $credentials, [], [], [
            'HTTP_Accept' => 'application/json',
        ]);
        $response = app()->handle($request);
        $decodedResponse = json_decode($response->getContent(), true);
        if ($response->getStatusCode() != 200) {
            if ($decodedResponse['message'] === 'The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client.') {
                throw new AuthenticationException(__('Authentication exception'), __('Incorrect username or password'));
            }

            throw new AuthenticationException(__($decodedResponse['error'] ?? ''), __($decodedResponse['message']));
        }

        return $decodedResponse;
    }

    protected function getAuthModelFactory(): AuthModelFactory
    {
        return app(AuthModelFactory::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeAuthModelInstance()
    {
        return $this->getAuthModelFactory()->make();
    }

    protected function getAuthModelClass(): string
    {
        return $this->getAuthModelFactory()->getClass();
    }
}
