<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

/**
 * Class ValidationException
 *
 * @package Joselfonseca\LighthouseGraphQLPassport\Exceptions
 */
class ValidationException extends Exception implements RendersErrorsExtensions
{
    /**
     * @var
     */
    public $errors;

    /**
     * ValidationException constructor.
     *
     * @param $validator
     * @param string $message
     */
    public function __construct($errors, string $message = "")
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
     * @return bool
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return 'validation';
    }

    /**
     * @return array
     */
    public function extensionsContent(): array
    {
        return ['errors' => $this->errors];
    }
}