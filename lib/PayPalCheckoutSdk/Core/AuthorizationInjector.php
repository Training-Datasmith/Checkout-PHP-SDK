<?php

declare(strict_types=1);

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpClient;
use PayPalHttp\HttpRequest;
use PayPalHttp\Injector;

class AuthorizationInjector implements Injector
{
    private $client;
    public $accessToken;

    public function __construct(HttpClient $client, private readonly PayPalEnvironment $environment, private $refreshToken)
    {
        $this->client = $client;
    }

    public function inject($request): void
    {
        if (!$this->hasAuthHeader($request) && !$this->isAuthRequest($request)) {
            if (is_null($this->accessToken) || $this->accessToken->isExpired()) {
                $this->accessToken = $this->fetchAccessToken();
            }
            $request->headers['Authorization'] = 'Bearer ' . $this->accessToken->token;
        }
    }

    private function fetchAccessToken(): \PayPalCheckoutSdk\Core\AccessToken
    {
        $accessTokenResponse = $this->client->execute(new AccessTokenRequest($this->environment, $this->refreshToken));
        $accessToken = $accessTokenResponse->result;
        return new AccessToken($accessToken->access_token, $accessToken->token_type, $accessToken->expires_in);
    }

    private function isAuthRequest($request): bool
    {
        return $request instanceof AccessTokenRequest || $request instanceof RefreshTokenRequest;
    }

    private function hasAuthHeader(HttpRequest $request): bool
    {
        return array_key_exists('Authorization', $request->headers);
    }
}
