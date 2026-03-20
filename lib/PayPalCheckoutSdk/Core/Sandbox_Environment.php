<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

class Sandbox_Environment extends Pay_Pal_Environment
{
    public function base_url(): string
    {
        return 'https://api.sandbox.paypal.com';
    }
}