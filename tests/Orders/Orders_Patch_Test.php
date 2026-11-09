<?php

declare(strict_types=1);

namespace Test\Orders;

use Pay_Pal_Checkout_Sdk\Orders\Orders_Get_Request;
use Pay_Pal_Checkout_Sdk\Orders\Orders_Patch_Request;
use PHPUnit\Framework\TestCase;
use Test\Test_Harness;

class Orders_Patch_Test extends TestCase
{
    /**
     * @return list<array<string, mixed>>
     */
    private function buildRequestBody(): array
    {
        return [
            [
                'op' => 'add',
                'path' => "/purchase_units/@reference_id=='test_ref_id1'/description",
                'value' => 'added_description',
            ],
            [
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='test_ref_id1'/amount",
                'value' => [
                    'currency_code' => 'USD',
                    'value' => '200.00',
                ],
            ],
        ];
    }

    public function testOrdersPatchRequest(): void
    {
        $client = Test_Harness::client();
        $createdOrder = Orders_Create_Test::create($client);

        $request = new Orders_Patch_Request($createdOrder->result->id);
        $request->body = $this->buildRequestBody();
        $response = $client->execute($request);
        $this->assertEquals(204, $response->statusCode);

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
        $this->assertEquals('200.00', $firstPurchaseUnit->amount->value);
        $this->assertEquals('added_description', $firstPurchaseUnit->description);

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
