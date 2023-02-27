<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

class AuthenticationException extends Exception implements ClientAware, ProvidesExtensions
{
    /**
     * @var @string
     */
    private $reason;

    public function __construct(string $message, string $reason)
    {
        parent::__construct($message);

        $this->reason = $reason;
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
     * Return the content that is put in the "extensions" part
     * of the returned error.
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return [
            'reason'       => $this->reason,
        ];
    }
}
