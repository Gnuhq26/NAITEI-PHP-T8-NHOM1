<?php

namespace Tests\Unit;

use App\Services\ShippingService;
use PHPUnit\Framework\TestCase;

class ShippingServiceTest extends TestCase
{
    protected $shippingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shippingService = new ShippingService();
    }

    /** @test */
    public function it_calculates_free_shipping_for_orders_under_5_million()
    {
        // < 5m -> free ship
        $result = $this->shippingService->calculateShippingFee(4999999);
        $this->assertEquals(0, $result);

        $result = $this->shippingService->calculateShippingFee(1000000);
        $this->assertEquals(0, $result);
    }

    /** @test */
    public function it_calculates_200k_shipping_for_orders_between_5_and_10_million()
    {
        // 5m - 10m -> 200k ship
        $result = $this->shippingService->calculateShippingFee(5000000);
        $this->assertEquals(200000, $result);

        $result = $this->shippingService->calculateShippingFee(7500000);
        $this->assertEquals(200000, $result);

        $result = $this->shippingService->calculateShippingFee(9999999);
        $this->assertEquals(200000, $result);
    }

    /** @test */
    public function it_calculates_500k_shipping_for_orders_between_10_and_20_million()
    {
        // 10m - 20m -> 500k ship
        $result = $this->shippingService->calculateShippingFee(10000000);
        $this->assertEquals(500000, $result);

        $result = $this->shippingService->calculateShippingFee(15000000);
        $this->assertEquals(500000, $result);

        $result = $this->shippingService->calculateShippingFee(19999999);
        $this->assertEquals(500000, $result);
    }

    /** @test */
    public function it_calculates_800k_shipping_for_orders_above_20_million()
    {
        // > 20m -> 800k ship
        $result = $this->shippingService->calculateShippingFee(20000000);
        $this->assertEquals(800000, $result);

        $result = $this->shippingService->calculateShippingFee(50000000);
        $this->assertEquals(800000, $result);
    }

    /** @test */
    public function it_returns_complete_shipping_info()
    {
        $totalAmount = 7500000;
        $result = $this->shippingService->getShippingInfo($totalAmount);

        $this->assertEquals($totalAmount, $result['subtotal']);
        $this->assertEquals(200000, $result['shipping_fee']);
        $this->assertEquals($totalAmount + 200000, $result['total']);
        $this->assertFalse($result['is_free_shipping']);
        $this->assertEquals('standard', $result['shipping_tier']);
    }

    /** @test */
    public function it_calculates_amount_needed_for_free_shipping()
    {
        $result = $this->shippingService->getAmountForFreeShipping(3000000);
        $this->assertEquals(2000000, $result);

        $result = $this->shippingService->getAmountForFreeShipping(6000000);
        $this->assertNull($result);
    }
}
