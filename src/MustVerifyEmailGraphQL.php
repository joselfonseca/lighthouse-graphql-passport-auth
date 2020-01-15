<?php

namespace Joselfonseca\LighthouseGraphQLPassport;

use Joselfonseca\LighthouseGraphQLPassport\Notifications\VerifyEmail;

trait MustVerifyEmailGraphQL
{
    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }
}
