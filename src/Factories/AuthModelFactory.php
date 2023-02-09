<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Factories;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;

class AuthModelFactory implements \Joselfonseca\LighthouseGraphQLPassport\Contracts\AuthModelFactory
{
    /**
     * @var Repository
     */
    private $config;

    public function __construct(
        Repository $config
    ) {
        $this->config = $config;
    }

    public function make(array $attributes = []): Model
    {
        $class = $this->getClass();

        return new $class($attributes);
    }

    public function create(array $attributes = []): Model
    {
        $model = $this->make($attributes);
        $model->save();

        return $model;
    }

    public function getClass(): string
    {
        $provider = $this->config->get('lighthouse-graphql-passport.auth_provider', 'users');

        return $this->config->get("auth.providers.{$provider}.model");
    }
}
