<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | The passport client id to use for requesting tokens, this should
    | support the password grant
    |
    */
    'client_id' => env('PASSPORT_CLIENT_ID', '1'),
    /*
    |--------------------------------------------------------------------------
    | Client secret
    |--------------------------------------------------------------------------
    |
    | The passport client secret to use for requesting tokens, this should
    | support the password grant
    |
    */
    'client_secret' => env('PASSPORT_CLIENT_SECRET', null),
    /*
    |--------------------------------------------------------------------------
    | GraphQL schema
    |--------------------------------------------------------------------------
    |
    | File path of the GraphQL schema to be used, defaults to null so it uses
    | the default location
    |
    */
    'schema' => null,
    /*
    |--------------------------------------------------------------------------
    | Username Column
    |--------------------------------------------------------------------------
    |
    | What column should be use for the username in the users table to find
    | the user.
    |
    */
    'username' => 'email'
];