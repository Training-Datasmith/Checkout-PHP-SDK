<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use Pay_Pal_Http\Http_Request;
class Access_Token_Request extends Http_Request
{
    public function __construct(Pay_Pal_Environment $environment, $refresh_token = null)
    {
        parent::__construct('/v1/oauth2/token', 'POST');
        $this->headers['Authorization'] = 'Basic ' . $environment->authorization_string();
        $body = ['grant_type' => 'client_credentials'];
        if (!is_null($refresh_token)) {
            $body['grant_type'] = 'refresh_token';
            $body['refresh_token'] = $refresh_token;
        }
        $this->body = $body;
        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
}