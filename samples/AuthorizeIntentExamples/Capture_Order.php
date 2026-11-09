<?php

declare(strict_types=1);

namespace Sample\AuthorizeIntentExamples;

require __DIR__ . '/../../vendor/autoload.php';
use Pay_Pal_Checkout_Sdk\Payments\Authorizations_Capture_Request;
use Sample\Pay_Pal_Client;

class Capture_Order
{
    /**
     * Below method can be used to build the capture request body.
     * This request can be updated with required fields as per need.
     * Please refer API specs for more info.
     */
    public static function buildRequestBody()
    {
        return '{}';
    }

    /**
     * Below function can be used to capture order.
     * Valid Authorization id should be passed as an argument.
     */
    public static function captureOrder($authorizationId, $debug = false)
    {
        $request = new Authorizations_Capture_Request($authorizationId);
        $request->body = self::buildRequestBody();
        $client = Pay_Pal_Client::client();
        $response = $client->execute($request);

        if ($debug) {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Capture ID: {$response->result->id}\n";
            print "Links:\n";
            foreach ($response->result->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To toggle printing the whole response body comment/uncomment below line
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }
        return $response;
    }
}

/**
 * Driver function for invoking the capture flow.
 */
if (!count(debug_backtrace())) {
    Capture_Order::captureOrder('18A38324BV5456924', true);
}
