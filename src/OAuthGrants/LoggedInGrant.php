<?php

namespace Joselfonseca\LighthouseGraphQLPassport\OAuthGrants;

use DateInterval;
use Illuminate\Http\Request;
use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoggedInGrant extends AbstractGrant
{
    public function __construct(UserRepositoryInterface $userRepository, RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    /**
     * @inheritdoc
     */
    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL): ResponseTypeInterface
    {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request);
        // Finalize the requested scopes
        $scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());
        // Issue and persist new tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);
        // Inject tokens into response
        $responseType->setAccessToken($accessToken);
        if ($refreshToken !== null) {
            $responseType->setRefreshToken($refreshToken);
        }

        return $responseType;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return 'logged_in_grant';
    }

    /**
     * Passport clients list the grant types they accept in their `grant_types`
     * column, and league/oauth2-server 9 refuses any grant that is not listed.
     * This grant is an extension of the password grant, so it accepts any
     * client permitted to use that grant rather than requiring applications to
     * add `logged_in_grant` to every existing client.
     *
     * {@inheritdoc}
     */
    protected function supportsGrantType(ClientEntityInterface $client, string $grantType): bool
    {
        return parent::supportsGrantType($client, $grantType)
            || parent::supportsGrantType($client, 'password');
    }

    /**
     * @param  ServerRequestInterface  $request
     * @return UserEntityInterface
     *
     * @throws OAuthServerException
     */
    protected function validateUser(ServerRequestInterface $request): UserEntityInterface
    {
        $laravelRequest = new Request($request->getParsedBody());
        $user = $this->getUserEntityByRequest($laravelRequest);
        if (false === $user instanceof UserEntityInterface) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidCredentials();
        }

        return $user;
    }

    /**
     * Retrieve user by request.
     *
     * @param  Request  $request
     * @return null|User
     *
     * @throws OAuthServerException
     */
    protected function getUserEntityByRequest(Request $request): ?User
    {
        if (is_null($model = config('auth.providers.users.model'))) {
            throw OAuthServerException::serverError('Unable to determine user model from configuration.');
        }
        if (method_exists($model, 'byLoggedInUser')) {
            $user = (new $model())->byLoggedInUser($request);
        } else {
            throw OAuthServerException::serverError('Unable to find byLoggedInUser method on user model.');
        }

        return ($user) ? new User((string) $user->getKey()) : null;
    }
}
