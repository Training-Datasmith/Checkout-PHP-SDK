<?php

declare(strict_types=1);

namespace Sample\CaptureIntentExamples;

require __DIR__ . '/../../vendor/autoload.php';
use Pay_Pal_Checkout_Sdk\Orders\Orders_Capture_Request;
use Sample\Pay_Pal_Client;

class Capture_Order
{
    /**
     * This function can be used to capture an order payment by passing the approved
     * order id as argument.
     *
     * @param orderId
     * @param debug
     * @returns
     */
    public static function captureOrder($orderId, $debug = false)
    {
        $request = new Orders_Capture_Request($orderId);

        $client = Pay_Pal_Client::client();
        $response = $client->execute($request);
        if ($debug) {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Links:\n";
            foreach ($response->result->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            print "Capture Ids:\n";
            foreach ($response->result->purchase_units as $purchase_unit) {
                foreach ($purchase_unit->payments->captures as $capture) {
                    print "\t{$capture->id}";
                }
            }
            // To toggle printing the whole response body comment/uncomment below line
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }

        return $response;
    }
}

/**
 * This is the driver function which invokes the captureOrder function with
 * <b>Approved</b> Order Id to capture the order payment.
 */
if (!count(debug_backtrace())) {
    Capture_Order::captureOrder('0F105083N67049335', true);
}
