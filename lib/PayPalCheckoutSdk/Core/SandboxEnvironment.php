<?php

declare(strict_types=1);

namespace PayPalCheckoutSdk\Core;

class SandboxEnvironment extends PayPalEnvironment
{
    public function baseUrl(): string
    {
        return 'https://api.sandbox.paypal.com';
    }
}
