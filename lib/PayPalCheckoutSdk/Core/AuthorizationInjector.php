<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use Pay_Pal_Http\Http_Client;
use Pay_Pal_Http\Http_Request;
use Pay_Pal_Http\Injector;
class Authorization_Injector implements Injector
{
    private $client;
    private $access_token;
    public function __construct(Http_Client $client, private readonly Pay_Pal_Environment $environment, private $refresh_token)
    {
        $this->client = $client;
    }
    public function inject($request): void
    {
        if (!$this->has_auth_header($request) && !$this->is_auth_request($request)) {
            if (is_null($this->access_token) || $this->access_token->is_expired()) {
                $this->access_token = $this->fetch_access_token();
            }
            $request->headers['Authorization'] = 'Bearer ' . $this->access_token->token;
        }
    }
    private function fetch_access_token(): \Pay_Pal_Checkout_Sdk\Core\Access_Token
    {
        $access_token_response = $this->client->execute(new Access_Token_Request($this->environment, $this->refresh_token));
        $access_token = $access_token_response->result;
        return new Access_Token($access_token->access_token, $access_token->token_type, $access_token->expires_in);
    }
    private function is_auth_request($request): bool
    {
        return $request instanceof Access_Token_Request || $request instanceof Refresh_Token_Request;
    }
    private function has_auth_header(Http_Request $request): bool
    {
        return array_key_exists('Authorization', $request->headers);
    }
}