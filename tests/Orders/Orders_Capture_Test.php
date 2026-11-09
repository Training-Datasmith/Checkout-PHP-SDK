<?php

declare(strict_types=1);

namespace Test\Orders;

use Pay_Pal_Checkout_Sdk\Orders\Orders_Capture_Request;
use PHPUnit\Framework\TestCase;
use Test\Test_Harness;

class Orders_Capture_Test extends TestCase
{
    public function testOrdersCaptureRequest(): void
    {
        $request = new Orders_Capture_Request('ORDER-ID');

        $client = Test_Harness::client();
        $response = $client->execute($request);
        $this->assertEquals(201, $response->statusCode);
        $this->assertNotNull($response->result);
    }
}
