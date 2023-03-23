<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

/**
 * Class ValidationException.
 */
class ValidationException extends Exception implements ClientAware, ProvidesExtensions
{
    /**
     * @var
     */
    public $errors;

    /**
     * ValidationException constructor.
     *
     * @param $validator
     * @param  string  $message
     */
    public function __construct($errors, string $message = '')
    {
        parent::__construct($message);

        $this->errors = $errors;
    }

    /**
     * The category.
     *
     * @var string
     */
    protected $category = 'validation';

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
     * @return array
     */
    public function getExtensions(): array
    {
        return ['errors' => $this->errors];
    }
}
