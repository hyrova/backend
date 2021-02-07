<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AccountTest extends TestCase
{
    private function createUser(): User
    {
        return User::factory()->make([
            'name' => 'Test',
            'password' => 'Test',
            'email' => 'test@mail.com',
        ]);
    }

    /*
     * Signup tests
     */

    public function testGuestCanSignup(): void
    {
        $response = $this->postJson('/api/signup', [
            'name' => 'Test',
            'email' => 'test@mail.com',
            'password' => 'Test'
        ]);

        $response->assertStatus(201);
    }

    public function testGuestCannotSignupWithAlreadyUsedNameOrEmail(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/signup', [
            'name' => 'Other',
            'email' => 'test@mail.com',
            'password' => 'Test'
        ]);

        $response->assertStatus(400);

        $response = $this->postJson('/api/signup', [
            'name' => 'Test',
            'email' => 'other@mail.com',
            'password' => 'Test'
        ]);

        $response->assertStatus(400);
    }

    public function testGuestCannotSignupWithInvalidParameters(): void
    {
        $response = $this->postJson('/api/signup', [
            'name' => 'invalid name',
            'email' => 'invalid mail',
            'e' => 'invalid parameter'
        ]);

        $response->assertStatus(422);
    }

    /*
     * Login tests
     */

    public function testUserCanLoginWithUsername(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'name' => 'Test',
            'password' => 'Test'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
            ]);
    }

    public function testUserCanLoginWithEmail(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => 'test@mail.com',
            'password' => 'Test'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
            ]);
    }

    public function testUserCannotLoginWithIncorrectCredentials(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => 'test@mail.com',
            'password' => 'invalid'
        ]);

        $response->assertStatus(400);
    }

    public function testUserCannotLoginWithInvalidParameters(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid mail',
            'e' => 'invalid parameter'
        ]);

        $response->assertStatus(422);
    }

    public function testUserCannotLoginIfBanned(): void
    {
        User::factory()->make([
            'name' => 'Test',
            'password' => 'Test',
            'email' => 'test@mail.com',
            'deleted_at' => now()
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@mail.com',
            'password' => 'Test'
        ]);

        $response->assertStatus(401);
    }

    /*
     * Reset password tests
     */

    public function testUserCanResetPassword()
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test@mail.com'
        ]);

        $response->assertStatus(200);

        $token = Password::createToken($user);

        $response = $this->postJson("/api/reset-password", [
            'token' => $token,
            'email' => 'test@mail.com',
            'password' => 'newpass'
        ]);

        $response->assertStatus(200);
    }

    public function testUserCannotResetPasswordWithIncorrectInformation(): void
    {
        // TODO whole reset password feature
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test@mail.com'
        ]);

        $response->assertStatus(400);

        $token = '123';

        $response = $this->postJson("/api/reset-password", [
            'token' => $token,
            'email' => 'test@mail.com',
            'password' => 'newpass'
        ]);

        $response->assertStatus(400);
    }


    public function testUserCannotResetPasswordWithInvalidParameters(): void
    {
        // TODO whole reset password feature
        $this->createUser();

        $response = $this->postJson('/api/forgot-password', [
            'e' => 'test@mail.com'
        ]);

        $response->assertStatus(422);

        $response = $this->postJson("/api/reset-password", [
            'e' => 'test@mail.com',
            'password' => 'pass'
        ]);

        $response->assertStatus(422);
    }

    /*
     * Profile tests
     */

    public function testUserCanGetHisProfile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->getJson('/api/user/1');

        $response->assertStatus(200);
    }

    public function testUserCannotGetOthersProfile(): void
    {
        $this->createUser();
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->getJson('/api/user/1');

        $response->assertStatus(401);
    }

    public function testUserCanUpdateHisProfile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->putJson('/api/user/1', [
                'email' => 'newmail@mail.com'
            ]);

        $user->refresh();

        self::assertEquals('newmail@mail.com', $user->email);

        $response->assertStatus(200);
    }

    public function testUserCannotUpdateOthersProfile(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $response = $this->actingAs($user2)
            ->putJson('/api/user/1', [
                'email' => 'newmail@mail.com'
            ]);

        self::assertNotEquals('newmail@mail.com', $user1->email);

        $response->assertStatus(401);
    }

    /*
     * Newsletter test
     */

    public function testUserCanSubscribeToNewsletter(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->putJson('/api/newsletter', [
                'subscribe' => true
            ]);

        $response->assertStatus(200);
    }

    public function testUserCanUnsubscribeToNewsletter(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->putJson('/api/newsletter', [
                'subscribe' => false
            ]);

        $response->assertStatus(200);
    }
}
