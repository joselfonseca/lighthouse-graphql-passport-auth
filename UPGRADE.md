# Upgrade Guide

## Upgrading To 11.0 From 10.x

Version 11 exists to support Laravel Passport 13. Passport 13 changes a number
of things the package depends on, and most of the work in this upgrade is
adapting an application to those changes rather than to the package itself. The
GraphQL schema is unchanged, and so are the errors the mutations return.

### Minimum Versions

The following minimum versions have been raised:

- PHP 8.2
- Laravel 11.35, 12 or 13
- Passport 13
- Lighthouse 6

Passport 12 is no longer supported. Its client repository, its `oauth_clients`
schema and its OAuth error payloads all differ from Passport 13's, and the
package no longer carries the code to work with both.

### The Authenticatable Model Must Implement `OAuthenticatable`

Passport 13 requires the model behind the configured auth provider to implement
`Laravel\Passport\Contracts\OAuthenticatable`, in addition to the `HasApiTokens`
trait it already used:

```php
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens;
}
```

### Clients Are Identified By A UUID

Passport 13 gives `oauth_clients` a UUID primary key. The package sends
`lighthouse-graphql-passport.client_id` with every token request, so
`PASSPORT_CLIENT_ID` is now a UUID rather than a small integer, and the setting
no longer has a default value of `1`.

Retrofitting UUIDs onto clients that `oauth_access_tokens` and `oauth_auth_codes`
already reference is not something an application should attempt casually.
Setting `Passport::$clientUuids = false` keeps auto incrementing ids and is the
simpler path for an existing installation.

### Client Secrets Are Always Hashed

Passport 13 stores only a hash of a client secret. The package sends
`lighthouse-graphql-passport.client_secret` with every token request, so
`PASSPORT_CLIENT_SECRET` must hold the plain text secret issued at the moment the
client was created. The value in the `oauth_clients` table is a hash and will not
authenticate.

Run `php artisan passport:hash` to hash the secrets of an existing installation.
Any secret that was not recorded when its client was created has to be
regenerated.

### The Client Must Carry The Password Grant

Passport 13 records the grants a client accepts in `oauth_clients.grant_types`,
and `league/oauth2-server` refuses any grant the client does not list. A client
that cannot use the `password` grant will be rejected with `unauthorized_client`.

Create one with `php artisan passport:client --password`.

The package's own `logged_in_grant` and `social_grant` do not need to be added to
the column. Both are extensions of the password grant and accept any client
permitted to use it, so existing clients continue to work without a data
migration.

### Key File Permissions Are Validated

`Passport::$validateKeyPermissions` defaulted to `false` in Passport 12 and
defaults to `true` in Passport 13. The OAuth server now checks the mode of
`oauth-private.key` and `oauth-public.key` and raises a notice unless it is one
of `400`, `440`, `600`, `640` or `660`. Under `APP_DEBUG` that notice becomes an
exception and every token request fails.

Git does not carry permissions through a checkout, and the check is skipped
entirely on Windows, so this tends to appear only once a deployment or a CI run
reaches Linux:

```bash
chmod 600 storage/oauth-private.key storage/oauth-public.key
```

Setting `Passport::$validateKeyPermissions = false` restores the previous
behaviour where restricting the keys is not practical.

### Error Responses Are Unchanged

No action is needed here, but it is worth knowing why. `league/oauth2-server` 9
replaced the `message` key in OAuth error responses with `error_description`, and
reports an invalid refresh token through its own message rather than the generic
`invalid_grant` one. The package reads the new key and recognises both messages,
so the `Authentication exception` and `Incorrect username or password` errors
returned to API consumers are exactly as they were in version 10.

One error did change. When no client is configured at all, the package used to
surface `invalid_client`. `league/oauth2-server` 9 rejects the missing `client_id`
parameter before it looks any client up, so this is now `invalid_request`. A
genuine bad secret against a real client still returns `invalid_client`.
