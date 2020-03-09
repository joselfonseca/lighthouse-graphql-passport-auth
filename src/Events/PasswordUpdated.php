<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PasswordUpdated.
 */
class PasswordUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var
     */
    public $user;

    /**
     * PasswordUpdated constructor.
     *
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
