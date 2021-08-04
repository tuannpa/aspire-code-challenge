<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class ProductTest extends TestCase
{
    private string $apiRoute = 'api/v1/product';

    public function testProductCreatedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $productData = [
            'name' => 'Product test one 200 credit point',
            'type' => 'individual',
            'amount' => 35000000,
            'minimum_credit_point_requirement' => 187
        ];

        $this->json('POST', $this->apiRoute, $productData, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJson([
                'product' => $productData,
                'message' => 'Created a new product successfully'
            ]);
    }

    public function testProductListFetchedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $productData1 = [
            'type' => 'individual',
            'amount' => 38000000,
            'minimum_credit_point_requirement' => 164
        ];

        $productData2 = [
            'type' => 'individual',
            'amount' => 12000000,
            'minimum_credit_point_requirement' => 95
        ];

        $productData3 = [
            'type' => 'organization',
            'amount' => 75000000,
            'minimum_credit_point_requirement' => 201
        ];

        $testData = [$productData1, $productData2, $productData3];
        $products = [];

        foreach ($testData as $data) {
            $products[] = Product::factory()->create($data);
        }

        $page = 2;
        $itemsPerPage = 2;

        $this->json('GET', "$this->apiRoute?page=$page&itemsPerPage=$itemsPerPage", ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'products' => [
                    $products[0]->toArray()
                ],
                'total' => count($testData),
                'currentPage' => $page,
                'message' => 'Fetched products successfully'
            ]);
    }

    public function testSingleProductFetchedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $productData = [
            'type' => 'individual',
            'amount' => 12000000,
            'minimum_credit_point_requirement' => 95
        ];

        $product = Product::factory()->create($productData);

        $this->json('GET', "$this->apiRoute/$product->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'product' => $product->toArray(),
                'message' => 'Fetched a product successfully'
            ]);
    }

    public function testProductUpdatedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $productData = [
            'type' => 'individual',
            'amount' => 12000000,
            'minimum_credit_point_requirement' => 95
        ];

        $product = Product::factory()->create($productData);

        $payload = [
            'name' => 'Updated',
            'amount' => 98000000,
            'minimum_credit_point_requirement' => 282,
            'description' => 'edited'
        ];

        $updatedProduct = array_merge($product->toArray(), $payload);

        $this->json('PATCH', "$this->apiRoute/$product->id" , $payload, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'product' => $updatedProduct,
                'message' => 'Product updated successfully'
            ]);
    }

    public function testProductDeletedSuccessfully()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);
        $this->actingAs($user, 'api');

        $productData = [
            'type' => 'individual',
            'amount' => 12000000,
            'minimum_credit_point_requirement' => 95
        ];

        $product = Product::factory()->create($productData);

        $this->json('DELETE', "$this->apiRoute/$product->id", [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted'
            ]);
    }
}
