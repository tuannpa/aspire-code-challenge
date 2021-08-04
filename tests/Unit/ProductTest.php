<?php

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    private Product | null $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = new Product();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->product = null;
    }

    public function testProductFillAbleFields()
    {
        $expected = [
            'name',
            'type',
            'amount',
            'minimum_credit_point_requirement',
            'description'
        ];

        $this->assertEquals($expected, $this->product->getFillable());
    }
}
