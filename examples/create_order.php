<?php

declare(strict_types=1);

/**
 * Example: Create a PayPal Order using the Checkout PHP SDK (capture intent).
 *
 * Requires PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET environment variables.
 * Uses the PayPal sandbox environment — switch to Production_Environment for live.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Pay_Pal_Checkout_Sdk\Core\Pay_Pal_Http_Client;
use Pay_Pal_Checkout_Sdk\Core\Sandbox_Environment;
use Pay_Pal_Checkout_Sdk\Orders\Orders_Capture_Request;
use Pay_Pal_Checkout_Sdk\Orders\Orders_Create_Request;

$clientId     = (string) getenv('PAYPAL_CLIENT_ID');
$clientSecret = (string) getenv('PAYPAL_CLIENT_SECRET');

$environment = new Sandbox_Environment($clientId, $clientSecret);
$client      = new Pay_Pal_Http_Client($environment);

// --- Create Order ---
$createRequest = new Orders_Create_Request();
$createRequest->headers['Prefer'] = 'return=representation';
$createRequest->body = [
    'intent'         => 'CAPTURE',
    'purchase_units' => [
        [
            'amount' => [
                'currency_code' => 'USD',
                'value'         => '25.00',
            ],
            'description' => 'Widget purchase',
        ],
    ],
    'application_context' => [
        'return_url' => 'https://example.com/payment/success',
        'cancel_url' => 'https://example.com/payment/cancel',
    ],
];

$createResponse = $client->execute($createRequest);
$orderId = $createResponse->result->id;

echo "Created Order ID: {$orderId}" . PHP_EOL;
echo "Status:           " . $createResponse->result->status . PHP_EOL;

// In production, redirect the buyer to the approval URL:
foreach ($createResponse->result->links as $link) {
    if ($link->rel === 'approve') {
        echo "Approval URL:     " . $link->href . PHP_EOL;
        break;
    }
}

// --- Capture Order (after buyer approval) ---
// $captureRequest = new Orders_Capture_Request($orderId);
// $captureResponse = $client->execute($captureRequest);
// echo "Captured: " . $captureResponse->result->status . PHP_EOL;
