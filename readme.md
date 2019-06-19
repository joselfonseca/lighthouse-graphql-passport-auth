Lighthouse GraphQL Passport Auth (Laravel 5.8 / Lighthouse ^3.2)
===============================================


[![Build Status](https://travis-ci.org/joselfonseca/lighthouse-graphql-passport-auth.svg?branch=master)](https://travis-ci.org/joselfonseca/lighthouse-graphql-passport-auth)
[![Total Downloads](https://poser.pugx.org/joselfonseca/lighthouse-graphql-passport-auth/downloads.svg)](https://packagist.org/packages/joselfonseca/lighthouse-graphql-passport-auth)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

GraphQL mutations for Laravel Passport using Lighthouse PHP ^3.2.

## Installation

**Make sure you have [Laravel Passport](https://laravel.com/docs/5.8/passport) installed.**

To install run `composer require joselfonseca/lighthouse-graphql-passport-auth`.

ServiceProvider will be attached automatically

Add the following env vars to your .env

```
PASSPORT_CLIENT_ID=
PASSPORT_CLIENT_SECRET=
```

You are done with the installation!

## Default Schema

By default the schema is defined internally in the package, if you want to override the schema or resolvers, you can publish the package configuration and default schema by running:

```
php artisan vendor:publish --provider="Joselfonseca\LighthouseGraphQLPassport\Providers\LighthouseGraphQLPassportServiceProvider"
```

This command will publish a configuration file `lighthouse-graphql-passport.php` and a schema file in `/graphql/auth.graphgl` that looks like this:

```js
input LoginInput {
    username: String!
    password: String!
}

input RefreshTokenInput {
    refresh_token: String
}

type User {
    id: ID!
    name: String!
    email: String!
}

type AuthPayload {
    access_token: String!
    refresh_token: String!
    expires_in: Int!
    token_type: String!
    user: User!
}

type LogoutResponse {
    status: String!
    message: String
}

type ForgotPasswordResponse {
    status: String!
    message: String
}

input ForgotPasswordInput {
    email: String!
}

input NewPasswordWithCodeInput {
    email: String!
    token: String!
    password: String!
    password_confirmation: String!
}

extend type Mutation {
    login(data: LoginInput): AuthPayload! @field(resolver: "Joselfonseca\\LighthouseGraphQLPassport\\GraphQL\\Mutations\\Login@resolve")
    refreshToken(data: RefreshTokenInput): AuthPayload! @field(resolver: "Joselfonseca\\LighthouseGraphQLPassport\\GraphQL\\Mutations\\RefreshToken@resolve")
    logout: LogoutResponse! @field(resolver: "Joselfonseca\\LighthouseGraphQLPassport\\GraphQL\\Mutations\\Logout@resolve")
    forgotPassword(data: ForgotPasswordInput!): ForgotPasswordResponse! @field(resolver: "Joselfonseca\\LighthouseGraphQLPassport\\GraphQL\\Mutations\\ForgotPassword@resolve")
    updateForgottenPassword(data: NewPasswordWithCodeInput): ForgotPasswordResponse! @field(resolver: "Joselfonseca\\LighthouseGraphQLPassport\\GraphQL\\Mutations\\ResetPassword@resolve")
}
```

In the configuration file you can now set the schema file to be used for the exported one like this:

```php
    /*
    |--------------------------------------------------------------------------
    | GraphQL schema
    |--------------------------------------------------------------------------
    |
    | File path of the GraphQL schema to be used, defaults to null so it uses
    | the default location
    |
    */
    'schema' => base_path('graphql/auth.graphql')
```

This will allow you to change the schema and resolvers if needed.

## Usage

This will add 5 mutations to your GraphQL API

```js
extend type Mutation {
    login(data: LoginInput): AuthPayload!
    refreshToken(data: RefreshTokenInput): AuthPayload!
    logout: LogoutResponse!
    forgotPassword(data: ForgotPasswordInput!): ForgotPasswordResponse!
    updateForgottenPassword(data: NewPasswordWithCodeInput): ForgotPasswordResponse!
}
```

- **login:** Will allow your clients to log in by using the password grant client.
- **refreshToken:** Will allow your clients to refresh a passport token by using the password grant client.
- **logout:** Will allow your clients to invalidate a passport token.
- **forgotPassword:** Will allow your clients to request the forgot password email.
- **updateForgottenPassword:** Will allow your clients to update the forgotten password from the email received.

### Why the OAuth client is used in the backend and not from the client application?

When an application that needs to be re compiled and re deploy to stores like an iOS app needs to change the client for whatever reason, it becomes a blocker for QA or even brakes the production app if the client is removed. The app will not work until the new version with the updated keys is deployed. There are alternatives to store this configuration in the client but for this use case we are relying on the backend to be the OAuth client

## Change log

Please see the releases page [https://github.com/joselfonseca/lighthouse-graphql-passport-auth/releases](https://github.com/joselfonseca/lighthouse-graphql-passport-auth/releases)

## Tests

To run the test in this package, navigate to the root folder of the project and run

```bash
    composer install
```
Then

```bash
    vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email jose at ditecnologia dot com instead of using the issue tracker.

## Credits

- [Jose Luis Fonseca](https://github.com/joselfonseca)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](license.md) for more information.