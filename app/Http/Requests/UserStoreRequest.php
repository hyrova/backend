<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:5|max:32|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|max:32',
            'roles' => 'array'
        ];
    }
}
