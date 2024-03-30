<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class AuthenticateWithApiGuard
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guard('api')->check()) {
            $this->auth->shouldUse('api');
        }

        return $next($request);
    }
}
