<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->when(Auth::user()->isSuperAdmin(), $this->id),
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'newsletter' => $this->newsletter,
        ];
    }
}
