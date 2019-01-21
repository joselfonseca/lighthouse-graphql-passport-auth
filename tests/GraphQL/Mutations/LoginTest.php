<?php

namespace Joselfonseca\LighthouseGraphQLPassport\Tests\GraphQL\Mutations;

use Joselfonseca\LighthouseGraphQLPassport\Tests\User;
use Joselfonseca\LighthouseGraphQLPassport\Tests\TestCase;

class LoginTest extends TestCase
{

    function test_it_gets_access_token()
    {
        $this->createClient();
        User::create([
            'name' => 'Jose Fonseca',
            'email' => 'jose@example.com',
            'password' => bcrypt('123456789qq')
        ]);
        $response = $this->mutation('
            mutation { 
                login(data: {
                    "username": "jose@example.com",
                    "password": "123456789qq"
                }) {
                    access_token
                    refresh_token
                }
            }'
        );
        dd($response);
    }

}