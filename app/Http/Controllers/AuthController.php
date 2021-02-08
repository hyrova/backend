<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Service\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService): JsonResponse
    {
        $token = $authService->loginSanctum($request);

        if ($token) {
            return $this->success($token);
        }

        return $this->failure('Wrong credentials');
    }

    public function register(RegisterRequest $request, AuthService $authService): JsonResponse
    {
        $token = $authService->registerSanctum($request);

        if ($token) {
            return $this->success($token, 201);
        }

        return $this->failure('Something wrong happened');
    }
}
