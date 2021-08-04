<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    private string $apiRoute = 'api/v1/customer';

    public function testCustomerCreatedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $customerData = [
            'name' => 'Test 1',
            'phone_number' => '+84936627237',
            'address' => 'Abc address',
            'gender' => 'M',
            'date_of_birth' => '1994-10-05',
            'credit_point' => 200
        ];

        $this->json('POST', $this->apiRoute, $customerData, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJson([
                'customer' => $customerData,
                'message' => 'Created a new customer successfully'
            ]);
    }

    public function testCustomerListFetchedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $customerData1 = [
            'gender' => 'M',
            'credit_point' => 200,
        ];

        $customerData2 = [
            'gender' => 'F',
            'credit_point' => 150
        ];

        $customerData3 = [
            'gender' => 'F',
            'credit_point' => 175
        ];

        $testData = [$customerData1, $customerData2, $customerData3];
        $customers = [];

        foreach ($testData as $data) {
            $customers[] = Customer::factory()->create($data);
        }

        $page = 2;
        $itemsPerPage = 2;

        $this->json('GET', "$this->apiRoute?page=$page&itemsPerPage=$itemsPerPage", ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'customers' => [
                    $customers[0]->toArray()
                ],
                'total' => count($testData),
                'currentPage' => $page,
                'message' => 'Fetched customers successfully'
            ]);
    }

    public function testSingleCustomerFetchedSuccessfully()
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

        $this->json('GET', "$this->apiRoute/$customer->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'customer' => $customer->toArray(),
                'message' => 'Fetched a customer successfully'
            ]);
    }

    public function testCustomerUpdatedSuccessfully()
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

        $payload = [
            'name' => 'Updated',
            'phone_number' => '+84911111111',
            'address' => 'updated address address',
            'credit_point' => 300
        ];

        $this->json('PATCH', "$this->apiRoute/$customer->id" , $payload, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'customer' => array_merge($customer->toArray(), $payload),
                'message' => 'Customer updated successfully'
            ]);
    }

    public function testCustomerDeletedSuccessfully()
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

        $this->json('DELETE', "$this->apiRoute/$customer->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Customer deleted'
            ]);
    }
}
