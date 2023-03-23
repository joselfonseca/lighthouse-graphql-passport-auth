<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Events;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class UserLoggedIn.
 */
class UserLoggedIn
{
    /**
     * @var Authenticatable
     */
    public $user;

    /**
     * UserLoggedIn constructor.
     *
     * @param  Authenticatable  $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
