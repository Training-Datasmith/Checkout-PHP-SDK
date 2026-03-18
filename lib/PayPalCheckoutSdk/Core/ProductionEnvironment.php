<?php

namespace PayPalCheckoutSdk\Core;

class ProductionEnvironment extends PayPalEnvironment
{
    public function baseUrl(): string
    {
        return "https://api.paypal.com";
    }
}
