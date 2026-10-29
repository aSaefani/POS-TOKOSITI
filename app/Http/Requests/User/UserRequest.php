<?php

namespace App\Http\Requests\User;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.manage') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');
        $isCreate = $user === null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:191', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::enum(Role::class)],
            'is_active' => ['sometimes', 'boolean'],
            'password' => [$isCreate ? 'required' : 'nullable', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }
}
