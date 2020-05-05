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
    | the user. Optionally you can add a 'findForPassport' method on your
    | auth model to have a more fine grain control of the user retrieval.
    | See https://laravel.com/docs/7.x/passport#customizing-the-username-field
    |
    */
    'username' => 'email',
    /*
    |--------------------------------------------------------------------------
    | Migrations
    |--------------------------------------------------------------------------
    |
    | Use the provided migrations for the socialite providers
    | If you publish the migrations set this to false.
    |
    */
    'migrations' => true,
    /*
    |--------------------------------------------------------------------------
    | Settings for email verification
    |--------------------------------------------------------------------------
    |
    | Update this values for your use case
    |
    */
    'verify_email' => [
        'base_url' => env('FRONT_URL').'/email-verify',
    ],

];
