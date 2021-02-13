<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserCRUDTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create();
    }

    private function createAdminUser(): User
    {
        return User::factory()->admin()->create();
    }

    public function testIndexWorksForAdmin(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->getJson(route('admin.users.index'));

        $response
            ->assertSuccessful()
            ->assertJsonCount(1, $key = 'data');
    }

    public function testIndexDoesNotWorkForUser(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->getJson(route('admin.users.index'));

        $response->assertUnauthorized();
    }

    public function testIndexDoesNotWorkForAnyoneElse(): void
    {
        $response = $this->getJson(route('admin.users.index'));

        $response->assertUnauthorized();
    }

    public function testShowWorksForAdmin(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->getJson(route('admin.users.show', ['user' => $user]));

        $response
            ->assertSuccessful()
            ->assertSee($user->email);
    }

    public function testShowDoesNotWorkForUser(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->getJson(route('admin.users.show', ['user' => $user]));

        $response->assertUnauthorized();
    }

    public function testShowDoesNotWorkForAnyoneElse(): void
    {
        $response = $this->getJson(route('admin.users.index'));

        $response->assertUnauthorized();
    }

    private const USER_NAME = 'username';
    private const USER_EMAIL = 'test@mail.com';
    private const USER_PASSWORD = 'password';

    public function testStoreWorksForAdmin(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->postJson(route('admin.users.store', [
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ]));

        $response
            ->assertSuccessful()
            ->assertSee(self::USER_EMAIL);
    }

    public function testStoreDoesNotWorkForUser(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson(route('admin.users.store', [
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ]));

        $response->assertUnauthorized();
    }

    public function testStoreDoesNotWorkForAnyoneElse(): void
    {
        $response = $this->postJson(route('admin.users.store', [
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ]));

        $response->assertUnauthorized();
    }

    public function testUpdateWorksForAdmin(): void
    {
        $user = $this->createAdminUser();
        $update_user = User::factory()->create([
            'password' => 'original_password',
        ]);

        $response = $this->actingAs($user)->putJson(route('admin.users.update', [
            'user' => $update_user,
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]));

        $response
            ->assertSuccessful()
            ->assertSee(self::USER_EMAIL)
            ->assertSee(self::USER_EMAIL);

        $update_user->refresh();

        self::assertFalse(Hash::check('original_password', $update_user->password));

        self::assertTrue(Hash::check(self::USER_PASSWORD, $update_user->password));
    }

    public function testUpdateDoesNotWorkForUser(): void
    {
        $user = $this->createUser();
        $update_user = $this->createUser();

        $response = $this->actingAs($user)->putJson(route('admin.users.update', [
            'user' => $update_user,
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ]));

        $response->assertUnauthorized();
    }

    public function testUpdateDoesNotWorkForAnyoneElse(): void
    {
        $update_user = $this->createUser();

        $response = $this->putJson(route('admin.users.update', [
            'user' => $update_user,
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ]));

        $response->assertUnauthorized();
    }

    public function testDestroyWorksForAdmin(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->deleteJson(route('admin.users.destroy', [
            'user' => $user
        ]));

        $response
            ->assertSuccessful();

        $user->refresh();

        self::assertNotNull($user->deleted_at);
    }

    public function testDestroyDoesNotWorkForUser(): void
    {
        $admin = $this->createUser();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->deleteJson(route('admin.users.destroy', [
            'user' => $user
        ]));


        $response->assertUnauthorized();
    }

    public function testDestroyDoesNotWorkForAnyoneElse(): void
    {
        $user = $this->createUser();

        $response = $this->deleteJson(route('admin.users.destroy', [
            'user' => $user
        ]));

        $response->assertUnauthorized();
    }
}
