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
            ->has(
                Role::factory()->state([
                    'name' => 'Administrator',
                    'slug' => 'SUPER_ADMIN'
                ])
            )->create();

        self::assertTrue($user->isSuperAdmin());
    }
}
