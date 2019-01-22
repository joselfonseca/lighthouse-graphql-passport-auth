Lighthouse Graphql Passport Auth (Laravel 5.7+)
===============================================


[![Build Status](https://travis-ci.org/joselfonseca/lighthouse-graphql-passport-auth.svg?branch=master)](https://travis-ci.org/joselfonseca/lighthouse-graphql-passport-auth)
[![Total Downloads](https://poser.pugx.org/joselfonseca/lighthouse-graphql-passport-auth/downloads.svg)](https://packagist.org/packages/joselfonseca/lighthouse-graphql-passport-auth)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

GraphQL mutations for Laravel Passport using Lighthouse PHP

## Installation

**Make sure you have [Laravel Passport](https://laravel.com/docs/5.7/passport) installed.**

To install run `composer require joselfonseca/lighthouse-graphql-passport-auth`.

ServiceProvider will be attached automatically

Add the following env vars to your .env

```
PASSPORT_CLIENT_ID=
PASSPORT_CLIENT_SECRET=
```

You are done with the installation!

## Usage

This will add 3 mutations to your GraphQL API

```js
extend type Mutation {
    login(data: LoginInput): AuthPayload!
    refreshToken(data: RefreshTokenInput): AuthPayload!
    logout: LogoutResponse!
}
```

- **login:** Will allow your clients to log in by using the password grant client.
- **refreshToken:** Will allow your clients to refresh a passport token by using the password grant client.
- **logout:** Will allow your clients to invalidate a passport token.

### Why the OAuth client is used in the backend and not from the client application?

When an application that needs to be re compiled and re deploy to stores like an iOS app needs to change the client for whatever reason, it becomes a blocker for QA or even if the client is removed. The app will not work until the new version with the updated keys is deployed. There are alternatives to store this configuration in the client but fot this use case we are relying on the backend to be the OAuth client

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