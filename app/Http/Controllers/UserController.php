<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserForgotPasswordRequest;
use App\Http\Requests\UserResetPasswordRequest;
use App\Http\Requests\UserUpdateNewsletterSubscriptionRequest;
use App\Http\Requests\UserUpdateProfileRequest;
use App\Http\Resources\UserResource;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function getProfile(Request $request): UserResource
    {
        $user = $request->user();

        return new UserResource($user);
    }

    public function updateProfile(UserUpdateProfileRequest $request): UserResource
    {
        $user = $request->user();

        // If more data needs to be update, move that in a service
        $user->update($request->only('email'));
        $user->refresh();

        return new UserResource($user);
    }

    public function updateNewsletterSubscription(UserUpdateNewsletterSubscriptionRequest $request): UserResource
    {
        $user = $request->user();

        $user->update([
            'newsletter' => $request->get('subscribe')
        ]);

        return new UserResource($user);
    }

    public function forgotPassword(UserForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->failure('Reset link could not be sent');
        }

        return $this->success('Reset link sent');
    }

    public function resetPassword(UserResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function ($user, $password) {
                $user->fill([
                    'password' => $password
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->failure('Password could not be reset');
        }

        return $this->success('Password reset');
    }
}
