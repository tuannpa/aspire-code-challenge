<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function testRequiredFieldsForRegistration()
    {
        $this->json('POST', 'api/register', ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ]
            ]);
    }

    public function testPasswordConfirmation()
    {
        $userData = [
            'name' => 'Tuan Nguyen',
            'email' => 'npatuan.uit@gmail.com',
            'password' => 'password'
        ];

        $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'password' => ['The password confirmation does not match.']
                ]
            ]);
    }

    public function testUniqueEmail()
    {
        $userData = [
            'name' => 'Tuan Nguyen',
            'email' => 'npatuan.uit@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        $attempts = 2;

        // First create a user. Then create another new user with the exact same data including the same email address.
        for ($i = 0; $i < $attempts; $i++) {
            $response = $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json']);

            if ($i === 1) {
                $response->assertStatus(422)
                    ->assertJson([
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'email' => ['The email has already been taken.']
                        ]
                    ]);
            }
        }
    }

    public function testSuccessfulRegistration()
    {
        $userData = [
            'name' => 'Tuan Nguyen',
            'email' => 'npatuan.uit@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'access_token'
            ]);
    }

    public function testMustEnterEmailAndPassword()
    {
        $this->json('POST', 'api/login')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ]
            ]);
    }

    public function testSuccessfulLogin()
    {
        // Create a user.
        $user = User::factory()->create([
            'email' => 'npatuan.uit@gmail.com',
            'password' => bcrypt('password1')
        ]);

        // Use the created user for authentication.
        $loginData = ['email' => 'npatuan.uit@gmail.com', 'password' => 'password1'];

        $this->json('POST', 'api/login', $loginData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
                'access_token'
            ]);

        // Assert that the user is authenticated.
        $this->assertAuthenticated();
    }
}
