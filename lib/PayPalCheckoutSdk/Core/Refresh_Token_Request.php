<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use PayPalHttp\HttpRequest;
class Refresh_Token_Request extends HttpRequest
{
    public function __construct(Pay_Pal_Environment $environment, $authorization_code)
    {
        parent::__construct('/v1/identity/openidconnect/tokenservice', 'POST');
        $this->headers['Authorization'] = 'Basic ' . $environment->authorization_string();
        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $this->body = ['grant_type' => 'authorization_code', 'code' => $authorization_code];
    }
}