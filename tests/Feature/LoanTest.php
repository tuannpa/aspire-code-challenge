<?php

namespace Tests\Feature;

use App\Constants\LoanStatus;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class LoanTest extends TestCase
{
    private string $apiRoute = 'api/v1/loan';

    public function testLoanCreatedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $loanData = [
            'customer_id' => 1,
            'status' => LoanStatus::NEW,
            'duration' => '45',
            'amount' => 30000000,
            'product_id' => 1,
            'interest_rate' => 7.00
        ];

        $this->json('POST', $this->apiRoute, $loanData, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJson([
                'loan' => $loanData,
                'message' => 'Created a new loan successfully'
            ]);
    }

    public function testLoanListFetchedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $loanData1 = [
            'customer_id' => 1,
            'status' => LoanStatus::NEW,
            'duration' => '45',
            'amount' => 30000000,
            'product_id' => 1
        ];

        $loanData2 = [
            'customer_id' => 1,
            'status' => LoanStatus::NEW,
            'duration' => '15',
            'amount' => 12500000,
            'product_id' => 3
        ];

        $loanData3 = [
            'customer_id' => 2,
            'status' => LoanStatus::NEW,
            'duration' => '55',
            'amount' => 79500000,
            'product_id' => 1
        ];

        $testData = [$loanData1, $loanData2, $loanData3];
        $loans = [];

        foreach ($testData as $data) {
            $loans[] = Loan::factory()->create($data);
        }

        $page = 2;
        $itemsPerPage = 2;

        $this->json('GET', "$this->apiRoute?page=$page&itemsPerPage=$itemsPerPage", ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'loans' => [
                    $loans[0]->toArray()
                ],
                'total' => count($testData),
                'currentPage' => $page,
                'message' => 'Fetched loans successfully'
            ]);
    }

    public function testSingleLoanFetchedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $loanData = [
            'customer_id' => 1,
            'status' => LoanStatus::NEW,
            'duration' => '45',
            'amount' => 30000000,
            'product_id' => 1
        ];

        $loan = Loan::factory()->create($loanData);

        $this->json('GET', "$this->apiRoute/$loan->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'loan' => $loan->toArray(),
                'message' => 'Fetched a loan successfully'
            ]);
    }

    public function testLoanApprovedSuccessfully()
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
            'amount' => 32000000,
            'product_id' => $product->id
        ];

        $loan = Loan::factory()->create($loanData);

        $payload = [
            'status' => LoanStatus::APPROVED,
            'description' => 'This loan is valid for approval'
        ];

        $this->json('PATCH', "$this->apiRoute/$loan->id" , $payload, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'loan' => array_merge($loan->toArray(), $payload),
                'message' => 'Loan updated successfully'
            ]);
    }

    public function testLoanCompletedSuccessfully()
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
            'amount' => 32000000,
            'product_id' => $product->id,
            'interest_rate' => 7.00
        ];

        $loan = $this->json('POST', $this->apiRoute, $loanData, ['Accept' => 'application/json']);
        $loanData = $loan->json()['loan'];
        $loanId = $loanData['id'];

        $paymentData = [
            'customer_id' => $customer->id,
            'loan_id' => $loanId,
            'paid_amount' => 32000000,
            'due_date' => '2021-08-03 09:26:02',
            'repaid_date' => '2021-08-03 09:26:02'
        ];

        $payment = $this->json('POST', 'api/v1/payment', $paymentData, ['Accept' => 'application/json']);

        $payload = [
            'status' => LoanStatus::COMPLETED,
            'description' => 'This loan is valid for approval'
        ];

        $expected = [
            'loan' => array_merge($loanData, $payload),
            'message' => 'Loan updated successfully'
        ];

        $res = $this->json('PATCH', "$this->apiRoute/$loanId" , $payload, ['Accept' => 'application/json']);
        $res->assertStatus(200);
        $updatedLoan = $res->json();
        unset($updatedLoan['loan']['payment'], $updatedLoan['loan']['approved_by']);

        $this->assertJson(json_encode($expected), json_encode($updatedLoan));
    }

    public function testLoanDeletedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $loanData = [
            'customer_id' => 1,
            'status' => LoanStatus::NEW,
            'duration' => '45',
            'amount' => 30000000,
            'product_id' => 1,
            'interest_rate' => 7.00
        ];

        $loan = Loan::factory()->create($loanData);

        $this->json('DELETE', "$this->apiRoute/$loan->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Loan deleted'
            ]);
    }
}
