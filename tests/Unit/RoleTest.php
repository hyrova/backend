<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class RoleTest extends TestCase
{
    public function testUserIsNotSuperAdmin()
    {
        $user = User::factory()
            ->for(
                Role::factory()->create([
                    'name' => 'User',
                    'slug' => 'USER'
                ])
            )->create();

        self::assertFalse($user->isSuperAdmin());
    }

    public function testUserIsSuperAdmin()
    {
        $user = User::factory()
            ->for(
                Role::factory()->create([
                    'name' => 'Administrateur',
                    'slug' => 'SUPER_ADMIN'
                ])
            )->create();

        self::assertTrue($user->isSuperAdmin());
    }
}
