<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\Unit\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Joselfonseca\LighthouseGraphQLPassport\Contracts\AuthModelFactory;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;
use Joselfonseca\LighthouseGraphQLPassport\Tests\User;

class AuthModelFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AuthModelFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->app->make(AuthModelFactory::class);
    }

    /**
     * @test
     */
    public function canMakeAuthModel(): void
    {
        $email = 'jose@example.com';
        $model = $this->factory->make(['email' => $email]);

        self::assertInstanceOf(User::class, $model);
        self::assertEquals($email, $model->email);
    }

    /**
     * @test
     */
    public function canCreateAuthModel(): void
    {
        $model = $this->factory->create([
            'name'     => 'Jose Fonseca',
            'email'    => 'jose@example.com',
            'password' => Hash::make('123456789qq'),
        ]);

        self::assertInstanceOf(User::class, $model);
        self::assertEquals($model->count(), 1);
    }

    /**
     * @test
     */
    public function canGetAuthModelClass(): void
    {
        self::assertEquals($this->factory->getClass(), User::class);
    }
}
