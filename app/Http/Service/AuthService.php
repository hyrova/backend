<?php


namespace App\Http\Service;


use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{

    private function attemptCredentials($login, $password): bool
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return Auth::attempt(['email' => $login, 'password' => $password]);
        }

        return Auth::attempt(['name' => $login, 'password' => $password]);
    }

    public function loginSanctum(LoginRequest $loginRequest): ?string
    {
        $login = $loginRequest->get('login');
        $password = $loginRequest->get('password');
        $device = $loginRequest->get('device');

        if (!$this->attemptCredentials($login, $password)) {
            return null;
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        // Create new token
        $token = $user->createToken($device);

        // Delete old token from same device
        $user
            ->tokens()
            ->where('name', $device)
            ->delete();

        return $token->plainTextToken;
    }

    public function registerSanctum(RegisterRequest $registerRequest): ?string
    {
        $userParams = $registerRequest->only('name', 'email', 'password');
        $device_name = $registerRequest->get('device');
        $user = User::create($userParams);

        if (!$user) {
            return null;
        }

        return $user->createToken($device_name)->plainTextToken;
    }
}
