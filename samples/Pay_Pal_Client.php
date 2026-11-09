<?php

declare(strict_types=1);

namespace Sample;

use Pay_Pal_Checkout_Sdk\Core\Pay_Pal_Http_Client;
use Pay_Pal_Checkout_Sdk\Core\Sandbox_Environment;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

class Pay_Pal_Client
{
    /**
     * Returns PayPal HTTP client instance with environment which has access
     * credentials context. This can be used invoke PayPal API's provided the
     * credentials have the access to do so.
     */
    public static function client()
    {
        return new Pay_Pal_Http_Client(self::environment());
    }

    /**
     * Setting up and Returns PayPal SDK environment with PayPal Access credentials.
     * For demo purpose, we are using SandboxEnvironment. In production this will be
     * ProductionEnvironment.
     */
    public static function environment()
    {
        $clientId = getenv('CLIENT_ID') ?: '<<PAYPAL-CLIENT-ID>>';
        $clientSecret = getenv('CLIENT_SECRET') ?: '<<PAYPAL-CLIENT-SECRET>>';
        return new Sandbox_Environment($clientId, $clientSecret);
    }
}
