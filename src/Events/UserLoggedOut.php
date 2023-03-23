<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Events;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class UserLoggedOut.
 */
class UserLoggedOut
{
    /**
     * @var Authenticatable
     */
    public $user;

    /**
     * UserLoggedOut constructor.
     *
     * @param  Authenticatable  $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
