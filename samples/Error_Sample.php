<?php

declare(strict_types=1);

namespace Sample;

require __DIR__ . '/../vendor/autoload.php';

use Pay_Pal_Checkout_Sdk\Orders\Orders_Create_Request;
use PayPalHttp\HttpException;
use Sample\Pay_Pal_Client;

class Error_Sample
{
    public static function prettyPrint($jsonData, $pre = '')
    {
        $pretty = '';
        foreach ($jsonData as $key => $val) {
            $pretty .= $pre . ucfirst($key) .': ';
            if (strcmp(gettype($val), 'array') == 0) {
                $pretty .= "\n";
                $sno = 1;
                foreach ($val as $value) {
                    $pretty .= $pre . "\t" . $sno++ . ":\n";
                    $pretty .= self::prettyPrint($value, $pre . "\t\t");
                }
            } else {
                $pretty .= $val . "\n";
            }
        }
        return $pretty;
    }

    /**
     * Body has no required parameters (intent, purchase_units)
     */
    public static function createError1()
    {
        $request = new Orders_Create_Request();
        $request->body = '{}';
        print "Request Body: {}\n\n";

        print "Response:\n";
        try {
            $client = Pay_Pal_Client::client();
            $response = $client->execute($request);
        } catch (HttpException $exception) {
            $message = json_decode($exception->getMessage(), true);
            print "Status Code: {$exception->statusCode}\n";
            print(self::prettyPrint($message));
        }
    }

    /**
     * Body has invalid parameter value for intent
     */
    public static function createError2()
    {
        $request = new Orders_Create_Request();
        $request->body =  [
            'intent' => 'INVALID',
            'purchase_units' =>
                 [
                    0 =>
                         [
                            'amount' =>
                                 [
                                    'currency_code' => 'USD',
                                    'value' => '100.00',
                                ],
                        ],
                ],
        ];
        print "Request Body:\n" . json_encode($request->body, JSON_PRETTY_PRINT) . "\n\n";

        try {
            $client = Pay_Pal_Client::client();
            $response = $client->execute($request);
        } catch (HttpException $exception) {
            print "Response:\n";
            $message = json_decode($exception->getMessage(), true);
            print "Status Code: {$exception->statusCode}\n";
            print(self::prettyPrint($message));
        }

    }
}

if (!count(debug_backtrace())) {
    print "Calling createError1 (Body has no required parameters (intent, purchase_units))\n";
    Error_Sample::createError1();

    print "\n\nCalling createError2 (Body has invalid parameter value for intent)\n";
    Error_Sample::createError2();
}
