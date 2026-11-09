<?php

declare(strict_types=1);

namespace Test;

use Pay_Pal_Checkout_Sdk\Core\Pay_Pal_Http_Client;
use Pay_Pal_Checkout_Sdk\Core\Sandbox_Environment;

ini_set('error_reporting', (string) E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

class Test_Harness
{
    public static function client(): Pay_Pal_Http_Client
    {
        return new Pay_Pal_Http_Client(self::environment());
    }

    public static function environment(): Sandbox_Environment
    {
        $clientId = getenv('CLIENT_ID') ?: '<<PAYPAL-CLIENT-ID>>';
        $clientSecret = getenv('CLIENT_SECRET') ?: '<<PAYPAL-CLIENT-SECRET>>';

        return new Sandbox_Environment($clientId, $clientSecret);
    }
}
