<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Loan;
use Tests\TestCase;

class LoanTest extends TestCase
{
    private Loan | null $loan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loan = new Loan();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->loan = null;
    }

    public function testLoanFillAbleFields()
    {
        $expected = [
            'customer_id',
            'interest_rate',
            'description',
            'status',
            'duration',
            'amount',
            'approved_by',
            'product_id'
        ];

        $this->assertEquals($expected, $this->loan->getFillable());
    }

    public function testLoanCustomerRelationship()
    {
        $this->assertBelongsToRelationship($this->loan, 'customer', new Customer());
    }

    public function testLoanPaymentRelationship()
    {
        $this->assertHasOneRelationship($this->loan, 'payment');
    }
}
