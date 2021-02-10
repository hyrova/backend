<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private const USER_ORIGINAL_USERNAME = 'username';
    private const USER_ORIGINAL_EMAIL = 'test@mail.com';
    private const USER_ORIGINAL_PASSWORD = 'password';

    private function createUser(): User
    {
        return User::factory()->create([
            'name' => self::USER_ORIGINAL_USERNAME,
            'email' => self::USER_ORIGINAL_EMAIL,
            'password' => self::USER_ORIGINAL_PASSWORD,
        ]);
    }

    /*
     * Signup tests
     */

    public function testGuestCanSignup(): void
    {
        $response = $this->postJson('/api/signup', [
            'name' => self::USER_ORIGINAL_USERNAME,
            'email' => self::USER_ORIGINAL_EMAIL,
            'password' => self::USER_ORIGINAL_PASSWORD,
            'device' => 'device'
        ]);

        $response->assertStatus(201);
    }

    public function testGuestCannotSignupWithAlreadyUsedNameOrEmail(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/signup', [
            'name' => 'other_name',
            'email' => self::USER_ORIGINAL_EMAIL,
            'password' => self::USER_ORIGINAL_PASSWORD,
            'device' => 'device'
        ]);

        $response->assertStatus(422);

        $response = $this->postJson('/api/signup', [
            'name' => self::USER_ORIGINAL_USERNAME,
            'email' => 'other@mail.com',
            'password' => self::USER_ORIGINAL_PASSWORD,
            'device' => 'device'
        ]);

        $response->assertStatus(422);
    }

    public function testGuestCannotSignupWithInvalidParameters(): void
    {
        $response = $this->postJson('/api/signup', [
            'name' => 'invalid name',
            'email' => 'invalid mail',
            'e' => 'invalid parameter',
            'device' => 'device'
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
            'login' => self::USER_ORIGINAL_USERNAME,
            'password' => self::USER_ORIGINAL_PASSWORD,
            'device' => 'device'
        ]);

        $response->assertStatus(200);
    }

    public function testUserCanLoginWithEmail(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'login' => self::USER_ORIGINAL_EMAIL,
            'password' => self::USER_ORIGINAL_PASSWORD,
            'device' => 'device'
        ]);

        $response->assertStatus(200);
    }

    public function testUserCannotLoginWithIncorrectCredentials(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'login' => self::USER_ORIGINAL_EMAIL,
            'password' => 'invalid',
            'device' => 'device'
        ]);

        $response->assertStatus(400);
    }

    public function testUserCannotLoginWithInvalidParameters(): void
    {
        $response = $this->postJson('/api/login', [
            'login' => 'invalid mail',
            'e' => 'invalid parameter',
            'device' => 'device'
        ]);

        $response->assertStatus(422);
    }

    public function testUserCannotLoginIfBanned(): void
    {
        User::factory()->make([
            'name' => self::USER_ORIGINAL_USERNAME,
            'email' => self::USER_ORIGINAL_EMAIL,
            'password' => self::USER_ORIGINAL_PASSWORD,
            'deleted_at' => now()
        ]);

        $response = $this->postJson('/api/login', [
            'login' => self::USER_ORIGINAL_EMAIL,
            'password' => self::USER_ORIGINAL_PASSWORD,
            'device' => 'device'
        ]);

        $response->assertStatus(400);
    }

    /*
     * Reset password tests
     */

    public function testUserCanResetPassword(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/forgot-password', [
            'email' => self::USER_ORIGINAL_EMAIL
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::createToken($user);
        $new_password = 'newpassword';

        $response = $this->postJson("/api/reset-password", [
            'token' => $token,
            'email' => self::USER_ORIGINAL_EMAIL,
            'password' => $new_password
        ]);

        $response->assertStatus(200);

        $user->refresh();

        self::assertFalse(Hash::check(self::USER_ORIGINAL_PASSWORD, $user->password));

        self::assertTrue(Hash::check($new_password, $user->password));
    }

    public function testUserCannotResetPasswordWithIncorrectInformation(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'incorrect@mail.com'
        ]);

        $response->assertStatus(400);

        $token = '123';
        $new_password = 'newpassword';

        $response = $this->postJson("/api/reset-password", [
            'token' => $token,
            'email' => self::USER_ORIGINAL_EMAIL,
            'password' => $new_password
        ]);

        $response->assertStatus(400);

        self::assertFalse(Hash::check($new_password, $user->password));

        self::assertTrue(Hash::check(self::USER_ORIGINAL_PASSWORD, $user->password));
    }


    public function testUserCannotResetPasswordWithInvalidParameters(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/forgot-password', [
            'e' => self::USER_ORIGINAL_EMAIL
        ]);

        $response->assertStatus(422);

        $new_password = 'pass';

        $response = $this->postJson("/api/reset-password", [
            'e' => self::USER_ORIGINAL_EMAIL,
            'password' => $new_password
        ]);

        $response->assertStatus(422);

        self::assertFalse(Hash::check($new_password, $user->password));

        self::assertTrue(Hash::check(self::USER_ORIGINAL_PASSWORD, $user->password));
    }

    /*
     * Profile tests
     */

    public function testUserCanGetHisProfile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->getJson('/api/me');

        $response->assertStatus(200);
    }

    public function testUserCanUpdateHisProfile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->putJson('/api/me', [
                'email' => 'newmail@mail.com'
            ]);

        $user->refresh();

        $response->assertStatus(200);

        self::assertEquals('newmail@mail.com', $user->email);
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
