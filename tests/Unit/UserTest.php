<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    private User | null $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->user = null;
    }

    public function testUserFillAbleFields()
    {
        $expected = [
            'name',
            'email',
            'password',
        ];

        $this->assertEquals($expected, $this->user->getFillable());
    }

    public function testUserHiddenFields()
    {
        $expected = [
            'password',
            'remember_token'
        ];

        $this->assertEquals($expected, $this->user->getHidden());
    }

    public function testUserCastFields()
    {
        $expected = [
            'id' => 'int',
            'email_verified_at' => 'datetime',
        ];

        $this->assertEquals($expected, $this->user->getCasts());
    }
}
