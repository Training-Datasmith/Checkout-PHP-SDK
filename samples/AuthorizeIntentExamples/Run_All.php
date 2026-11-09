<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Sample\AuthorizeIntentExamples\Authorize_Order;
use Sample\AuthorizeIntentExamples\Capture_Order;
use Sample\AuthorizeIntentExamples\Create_Order;
use Sample\Refund_Order;

$order = Create_Order::createOrder();

print "Creating Order...\n";
$orderId = '';
if ($order->statusCode == 201) {
    $orderId = $order->result->id;
    print "Links:\n";
    for ($i = 0; $i < count($order->result->links); ++$i) {
        $link = $order->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
    print "Created Successfully\n";
    print "Copy approve link and paste it in browser. Login with buyer account and follow the instructions.\nOnce approved hit enter...\n";
} else {
    exit(1);
}

$handle = fopen('php://stdin', 'r');
$line = fgets($handle);
fclose($handle);

print "Authorizing Order...\n";
$response = Authorize_Order::authorizeOrder($orderId);
$authId = '';

if ($response->statusCode == 201) {
    print "Authorized Successfully\n";
    $authId = $response->result->purchase_units[0]->payments->authorizations[0]->id;
} else {
    exit(1);
}

print "\nCapturing Order...\n";
$response = Capture_Order::captureOrder($authId);
if ($response->statusCode == 201) {
    print "Captured Successfully\n";
    print "Status Code: {$response->statusCode}\n";
    print "Status: {$response->result->status}\n";
    $captureId = $response->result->id;
    print "Capture ID: {$captureId}\n";
    print "Links:\n";
    for ($i = 0; $i < count($response->result->links); ++$i) {
        $link = $response->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
} else {
    exit(1);
}

print "\nRefunding Order...\n";
$response = Refund_Order::refundOrder($captureId);
if ($response->statusCode == 201) {
    print "Refunded Successfully\n";
    print "Status Code: {$response->statusCode}\n";
    print "Status: {$response->result->status}\n";
    print "Refund ID: {$response->result->id}\n";
    print "Links:\n";
    for ($i = 0; $i < count($response->result->links); ++$i) {
        $link = $response->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
} else {
    exit(1);
}
