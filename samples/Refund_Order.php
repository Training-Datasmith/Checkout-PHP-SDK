<?php

declare(strict_types=1);

namespace Sample;

require __DIR__ . '/../vendor/autoload.php';
use Pay_Pal_Checkout_Sdk\Payments\Captures_Refund_Request;

class Refund_Order
{
    /**
     * Function to create an refund capture request. Payload can be updated to issue partial refund.
     */
    public static function buildRequestBody()
    {
        return [
            'amount' =>
                [
                    'value' => '20.00',
                    'currency_code' => 'USD',
                ],
        ];
    }

    /**
     * This function can be used to preform refund on the capture.
     */
    public static function refundOrder($captureId, $debug = false)
    {
        $request = new Captures_Refund_Request($captureId);
        $request->body = self::buildRequestBody();
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
            // To toggle printing the whole response body comment/uncomment below line
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }
        return $response;
    }
}

/**
 * This is the driver function which invokes the refund capture function with
 * Capture Id to perform refund on capture.
 */
if (!count(debug_backtrace())) {
    Refund_Order::refundOrder('8XL09935J2224701N', true);
}
