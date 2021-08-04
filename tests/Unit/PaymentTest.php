<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private Payment | null $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payment = new Payment();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->payment = null;
    }

    public function testPaymentFillAbleFields()
    {
        $expected = [
            'customer_id',
            'loan_id',
            'state',
            'due_date',
            'repaid_date',
            'paid_amount',
            'remaining_amount'
        ];

        $this->assertEquals($expected, $this->payment->getFillable());
    }

    public function testPaymentCustomerRelationship()
    {
        $this->assertBelongsToRelationship($this->payment, 'customer', new Customer());
    }

    public function testPaymentLoanRelationship()
    {
        $this->assertBelongsToRelationship($this->payment, 'loan', new Loan());
    }
}
