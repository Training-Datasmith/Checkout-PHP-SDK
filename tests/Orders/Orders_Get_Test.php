<?php

declare(strict_types=1);

namespace Test\Orders;

use Pay_Pal_Checkout_Sdk\Orders\Orders_Get_Request;
use PHPUnit\Framework\TestCase;
use Test\Test_Harness;

class Orders_Get_Test extends TestCase
{
    public function testOrdersGetRequest(): void
    {
        $client = Test_Harness::client();
        $createdOrder = Orders_Create_Test::create($client);

        $request = new Orders_Get_Request($createdOrder->result->id);
        $response = $client->execute($request);
        $this->assertEquals(200, $response->statusCode);
        $this->assertNotNull($response->result);

        $createdOrder = $response->result;
        $this->assertNotNull($createdOrder->id);
        $this->assertNotNull($createdOrder->purchase_units);
        $this->assertCount(1, $createdOrder->purchase_units);
        $firstPurchaseUnit = $createdOrder->purchase_units[0];
        $this->assertEquals('test_ref_id1', $firstPurchaseUnit->reference_id);
        $this->assertEquals('USD', $firstPurchaseUnit->amount->currency_code);
        $this->assertEquals('100.00', $firstPurchaseUnit->amount->value);

        $this->assertNotNull($createdOrder->create_time);
        $this->assertNotNull($createdOrder->links);
        $foundApproveUrl = false;
        foreach ($createdOrder->links as $link) {
            if ('approve' === $link->rel) {
                $foundApproveUrl = true;
                $this->assertNotNull($link->href);
                $this->assertEquals('GET', $link->method);
            }
        }
        $this->assertTrue($foundApproveUrl);
        $this->assertEquals('CREATED', $createdOrder->status);
    }
}
