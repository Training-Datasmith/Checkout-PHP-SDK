<?php

declare(strict_types=1);

/**
 * Example: Create a PayPal Order using the Checkout PHP SDK (capture intent).
 *
 * Requires PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET environment variables.
 * Uses the PayPal sandbox environment — switch to ProductionEnvironment for live.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

$clientId     = (string) getenv('PAYPAL_CLIENT_ID');
$clientSecret = (string) getenv('PAYPAL_CLIENT_SECRET');

$environment = new SandboxEnvironment($clientId, $clientSecret);
$client      = new PayPalHttpClient($environment);

// --- Create Order ---
$createRequest = new OrdersCreateRequest();
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
// $captureRequest = new OrdersCaptureRequest($orderId);
// $captureResponse = $client->execute($captureRequest);
// echo "Captured: " . $captureResponse->result->status . PHP_EOL;
