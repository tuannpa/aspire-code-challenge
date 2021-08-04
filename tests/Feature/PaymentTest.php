<?php

namespace Tests\Feature;

use App\Constants\LoanStatus;
use App\Constants\PaymentStatus;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private string $apiRoute = 'api/v1/payment';

    private array $relatedData = [];

    private function createPaymentRelatedData(array $config = []): array
    {
        $customerData = [
            'gender' => 'M',
            'credit_point' => 200
        ];

        $customer = Customer::factory()->create($customerData);

        $productData = [
            'type' => 'individual',
            'amount' => 12000000,
            'minimum_credit_point_requirement' => 95
        ];

        $product = Product::factory()->create($productData);

        $loanAmount = 32000000;

        $loanData = [
            'customer_id' => $customer->id,
            'status' => $config['loan']['status'] ?? LoanStatus::NEW,
            'duration' => '45',
            'amount' => $loanAmount,
            'product_id' => $product->id
        ];

        $loan = Loan::factory()->create($loanData);

        return [
            'customer' => $customer,
            'product' => $product,
            'loan' => $loan
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->relatedData = $this->createPaymentRelatedData();
    }

    protected function tearDown(): void
    {
        $this->relatedData = [];
    }

    public function testPaymentCreatedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $paidAmount = 32000000;

        $paymentData = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => $paidAmount,
            'due_date' => '2021-08-02 10:21:14',
            'repaid_date' => '2021-08-02 10:21:14'
        ];

        $remainingAmount = $this->relatedData['loan']->amount - $paidAmount;
        $state = $remainingAmount === 0 ? PaymentStatus::PAID : PaymentStatus::PARTIALLY_PAID;

        $expected = array_merge(
            $paymentData,
            [
                'remaining_amount' => $remainingAmount,
                'state' => $state
            ]
        );

        $this->json('POST', $this->apiRoute, $paymentData, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJson([
                'payment' => $expected,
                'message' => 'Created a new payment successfully'
            ]);
    }

    public function testPaymentListFetchedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $paymentData1 = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => 2000000,
            'remaining_amount' => $this->relatedData['loan']->amount - 2000000
        ];

        $paymentData2 = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => 2400000,
            'remaining_amount' => $this->relatedData['loan']->amount - 2400000
        ];

        $paymentData3 = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => 5800000,
            'remaining_amount' => $this->relatedData['loan']->amount - 5800000
        ];

        $testData = [$paymentData1, $paymentData2, $paymentData3];
        $payments = [];

        foreach ($testData as $data) {
            $payments[] = Payment::factory()->create($data);
        }

        $page = 2;
        $itemsPerPage = 2;

        $this->json('GET', "$this->apiRoute?page=$page&itemsPerPage=$itemsPerPage", ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'payments' => [
                    $payments[0]->toArray()
                ],
                'total' => count($testData),
                'currentPage' => $page,
                'message' => 'Fetched payments successfully'
            ]);
    }

    public function testSinglePaymentFetchedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $paymentData = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => 2400000,
            'remaining_amount' => $this->relatedData['loan']->amount - 2400000
        ];

        $payment = Payment::factory()->create($paymentData);

        $this->json('GET', "$this->apiRoute/$payment->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'payment' => $payment->toArray(),
                'message' => 'Fetched a payment successfully'
            ]);
    }

    public function testPaymentUpdatedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $customerData = [
            'gender' => 'M',
            'credit_point' => 200
        ];

        $customer = Customer::factory()->create($customerData);

        $productData = [
            'type' => 'individual',
            'amount' => 12000000,
            'minimum_credit_point_requirement' => 95
        ];

        $product = Product::factory()->create($productData);

        $loanData = [
            'customer_id' => $customer->id,
            'status' => LoanStatus::NEW,
            'duration' => '45',
            'amount' => 30000000,
            'product_id' => $product->id,
            'interest_rate' => 7.00
        ];

        $loanResponse = $this->json('POST', 'api/v1/loan', $loanData, ['Accept' => 'application/json']);
        $loan = $loanResponse->json('loan');

        $paymentData = [
            'customer_id' => $customer->id,
            'loan_id' => $loan['id'],
            'state' => PaymentStatus::NEW,
            'paid_amount' => 2400000,
            'remaining_amount' => $loan['amount'] - 2400000
        ];

        $payment = Payment::factory()->create($paymentData)->toArray();

        $paidAmount = 8000000;
        $payload = [
            'loan_id' => $loan['id'],
            'paid_amount' => $paidAmount
        ];

        $expected = array_merge($payment, [
            'paid_amount' => $paidAmount + $payment['paid_amount'],
            'remaining_amount' => $payment['remaining_amount'] - $paidAmount,
            'state' => PaymentStatus::PARTIALLY_PAID,
        ]);

        $paymentId = $payment['id'];

        $res = $this->json('PATCH', "$this->apiRoute/$paymentId" , $payload, ['Accept' => 'application/json']);
            $res->assertStatus(200)
            ->assertJson([
                'payment' => $expected,
                'message' => 'Payment updated successfully'
            ]);
    }

    public function testPaymentUpdatedWithInvalidPaidAmount()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $paymentData = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => 32500000,
            'repaid_date' => '2021-08-02 10:21:14',
            'due_date' => '2021-08-02 10:21:14'
        ];

        $this->json('POST', $this->apiRoute , $paymentData, ['Accept' => 'application/json'])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'error' => 'Invalid paid_amount parameter: paid_mount cannot be greater than remaining amount'
            ]);
    }

    public function testPaymentUpdatedWithCompletedLoan()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $config = [
            'loan' => [
                'status' => LoanStatus::COMPLETED
            ]
        ];

        $this->relatedData = $this->createPaymentRelatedData($config);

        $paymentData = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => 32500000,
            'repaid_date' => '2021-08-02 10:21:14',
            'due_date' => '2021-08-02 10:21:14'
        ];

        $this->json('POST', $this->apiRoute , $paymentData, ['Accept' => 'application/json'])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'error' => 'Cannot create a new payment for a completed loan'
            ]);
    }

    public function testPaymentDeletedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $paymentData = [
            'customer_id' => $this->relatedData['customer']->id,
            'loan_id' => $this->relatedData['loan']->id,
            'state' => PaymentStatus::NEW,
            'paid_amount' => 2700000,
            'remaining_amount' => $this->relatedData['loan']->amount - 2700000
        ];

        $payment = Payment::factory()->create($paymentData);

        $this->json('DELETE', "$this->apiRoute/$payment->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Payment deleted'
            ]);
    }
}
