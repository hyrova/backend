<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminUserController extends Controller
{

    public function index(): UserCollection
    {
        return new UserCollection(User::paginate(10));
    }

    public function store(UserStoreRequest $request): UserResource
    {
        $user = User::create($request->validated());
        $user->roles()->sync($request->get('roles'));
        $user->save();
        $user->refresh();

        return new UserResource($user);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request, User $user): UserResource
    {
        if ($roles = $request->get('roles')) {
            $user->roles()->sync($roles);
        }

        $user->update($request->validated());

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->success('User soft deleted successfully');
    }
}
