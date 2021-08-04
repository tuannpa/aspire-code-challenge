<?php

namespace Tests\Unit;

use App\Models\Customer;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    private Customer | null $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = new Customer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->customer = null;
    }

    public function testCustomerFillAbleFields()
    {
        $expected = [
            'name',
            'phone_number',
            'address',
            'gender',
            'credit_point',
            'date_of_birth',
            'product_id'
        ];

        $this->assertEquals($expected, $this->customer->getFillable());
    }

    public function testCustomerLoanRelationship()
    {
        $this->assertHasManyRelationship($this->customer, 'loans');
    }

    public function testCustomerPaymentRelationship()
    {
        $this->assertHasManyRelationship($this->customer, 'payments');
    }
}
