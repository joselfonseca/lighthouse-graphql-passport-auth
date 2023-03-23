<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Notifications;

use Illuminate\Support\Carbon;

class VerifyEmail extends \Illuminate\Auth\Notifications\VerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $payload = $this->getToken($notifiable);

        return config('lighthouse-graphql-passport.verify_email.base_url').'?token='.$payload;
    }

    /**
     * Get a token for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function getToken($notifiable)
    {
        return base64_encode(json_encode([
            'id'         => $notifiable->getKey(),
            'hash'       => encrypt($notifiable->getEmailForVerification()),
            'expiration' => encrypt(Carbon::now()->addMinutes(10)->toIso8601String()),
        ]));
    }
}
