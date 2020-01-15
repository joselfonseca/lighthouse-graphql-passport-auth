<?php

namespace Joselfonseca\LighthouseGraphQLPassport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HasLoggedInTokens
{
    /**
     * @throws \Exception
     *
     * @return mixed
     */
    public function getTokens()
    {
        $request = Request::create('oauth/token', 'POST', [
            'grant_type'    => 'logged_in_grant',
            'client_id'     => config('lighthouse-graphql-passport.client_id'),
            'client_secret' => config('lighthouse-graphql-passport.client_secret'),
        ], [], [], [
            'HTTP_Accept' => 'application/json',
        ]);
        $response = app()->handle($request);

        return json_decode($response->getContent(), true);
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function byLoggedInUser($request)
    {
        return Auth::user();
    }
}
