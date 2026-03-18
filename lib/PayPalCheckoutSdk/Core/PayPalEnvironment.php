<?php

declare(strict_types=1);

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\Environment;

abstract class PayPalEnvironment implements Environment
{
    public function __construct(private $clientId, private $clientSecret)
    {
    }

    public function authorizationString()
    {
        return base64_encode($this->clientId . ':' . $this->clientSecret);
    }
}
