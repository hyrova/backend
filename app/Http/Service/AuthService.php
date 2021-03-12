<?php


namespace App\Http\Service;


use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
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

    public function loginSanctum(UserLoginRequest $request): ?string
    {
        $login = $request->get('login');
        $password = $request->get('password');
        $device = $request->get('device');

        if (!$this->attemptCredentials($login, $password)) {
            return null;
        }

        $user = $request->user();

        if (!$user) {
            return null;
        }

        // Delete old token from same device
        $user
            ->tokens()
            ->where('name', $device)
            ->delete();

        // Create new token
        return $user->createToken($device)->plainTextToken;
    }

    public function registerSanctum(UserRegisterRequest $request): ?string
    {
        $userParams = $request->only('name', 'email', 'password');
        $device_name = $request->get('device');
        $user = User::create($userParams);

        return $user->createToken($device_name)->plainTextToken;
    }
}
