<?php


namespace Joselfonseca\LighthouseGraphQLPassport;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

trait HasSocialLogin
{

    public static function byOAuthToken(Request $request)
    {
        $userData = Socialite::driver($request->get('provider'))->userFromToken($request->get('token'));
        try {
            $user = static::where('provider', $request->get('provider'))->where('provider_id', $userData->getId())->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $user = static::create([
                'name' => $userData->getName(),
                'email' => $userData->getEmail(),
                'provider' => $request->get('provider'),
                'provider_id' => $userData->getId(),
                'password' => bcrypt(Str::random(16))
            ]);
        }
        return $user;
    }

}
