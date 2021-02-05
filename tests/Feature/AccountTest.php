<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response->assertStatus(200);
    }

    public function testUserCanLoginWithEmail(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => 'test@mail.com',
            'password' => 'Test'
        ]);

        $response->assertStatus(200);
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

    public function testCannotLoginWithInvalidParameters(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid mail',
            'e' => 'invalid parameter'
        ]);

        $response->assertStatus(422);
    }

    /*
     * Reset password tests
     */

    public function testUserCanResetPassword(): void
    {
        // TODO whole reset password feature
        $this->createUser();

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test@mail.com'
        ]);

        $response->assertStatus(200);

        // TODO generate valid token
        $token = '123';

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
}
