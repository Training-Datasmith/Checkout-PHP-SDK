<?php

declare(strict_types=1);

namespace Sample;

require __DIR__ . '/../vendor/autoload.php';

use Pay_Pal_Checkout_Sdk\Orders\Orders_Get_Request;
use Pay_Pal_Checkout_Sdk\Orders\Orders_Patch_Request;
use Sample\AuthorizeIntentExamples\Create_Order;

class Patch_Order
{
    private static function buildRequestBody()
    {
        return  [
            0 =>
                 [
                    'op' => 'replace',
                    'path' => '/intent',
                    'value' => 'CAPTURE',
                ],
            1 =>
                 [
                    'op' => 'replace',
                    'path' => '/purchase_units/@reference_id==\'PUHF\'/amount',
                    'value' =>
                         [
                            'currency_code' => 'USD',
                            'value' => '200.00',
                            'breakdown' =>
                                 [
                                    'item_total' =>
                                         [
                                            'currency_code' => 'USD',
                                            'value' => '180.00',
                                        ],
                                    'tax_total' =>
                                         [
                                            'currency_code' => 'USD',
                                            'value' => '20.00',
                                        ],
                                ],
                        ],
                ],
        ];
    }

    public static function patchOrder($orderId)
    {

        $client = Pay_Pal_Client::client();

        $request = new Orders_Patch_Request($orderId);
        $request->body = Patch_Order::buildRequestBody();
        $client->execute($request);

        $response = $client->execute(new Orders_Get_Request($orderId));

        print "Status Code: {$response->statusCode}\n";
        print "Status: {$response->result->status}\n";
        print "Order ID: {$response->result->id}\n";
        print "Intent: {$response->result->intent}\n";
        print "Links:\n";
        foreach ($response->result->links as $link) {
            print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
        }

        print "Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n";

        // To toggle printing the whole response body comment/uncomment below line
        echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
    }
}

if (!count(debug_backtrace())) {
    print "Before PATCH:\n";
    $createdOrder = Create_Order::createOrder(true)->result;
    print "\nAfter PATCH (Changed Intent and Amount):\n";
    Patch_Order::patchOrder($createdOrder->id);
}
