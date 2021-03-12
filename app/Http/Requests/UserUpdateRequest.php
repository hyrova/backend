<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|min:5|max:32|unique:users',
            'email' => 'email|unique:users',
            'password' => 'string|min:8|max:32',
            'roles' => 'array'
        ];
    }
}
