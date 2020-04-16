<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class AuthenticationException extends Exception implements RendersErrorsExtensions
{
    /**
     * @var @string
     */
    private $reason;

    /**
     * @var @string
     */
    private $user_message;

    public function __construct(string $message, string $reason, string $user_message)
    {
        parent::__construct($message);

        $this->reason = $reason;

        $this->user_message = $user_message;
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @api
     *
     * @return bool
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     *
     * @api
     *
     * @return string
     */
    public function getCategory(): string
    {
        return 'authentication';
    }

    /**
     * Return the content that is put in the "extensions" part
     * of the returned error.
     *
     * @return array
     */
    public function extensionsContent(): array
    {
        return [
            'user_message' => $this->user_message,
            'reason'       => $this->reason,
        ];
    }
}
