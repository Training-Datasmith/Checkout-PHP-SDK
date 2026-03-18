<?php

declare(strict_types=1);

namespace PayPalCheckoutSdk\Core;

class ProductionEnvironment extends PayPalEnvironment
{
    public function baseUrl(): string
    {
        return 'https://api.paypal.com';
    }
}
