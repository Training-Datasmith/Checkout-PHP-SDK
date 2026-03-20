<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use Pay_Pal_Http\Environment;
abstract class Pay_Pal_Environment implements Environment
{
    public function __construct(private $client_id, private $client_secret)
    {
    }
    public function authorization_string()
    {
        return base64_encode($this->client_id . ':' . $this->client_secret);
    }
}