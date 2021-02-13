<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function testUserIsNotSuperAdmin(): void
    {
        $user = User::factory()
            ->create();

        self::assertFalse($user->isSuperAdmin());
    }

    public function testUserIsSuperAdmin(): void
    {
        $user = User::factory()
            ->admin()->create();

        self::assertTrue($user->isSuperAdmin());
    }
}
