<?php

namespace App\Http\Requests\Users;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-users');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            // Saat mengubah akun sendiri, field peran dinonaktifkan di form sehingga tidak terkirim.
            'role' => [Rule::requiredIf(fn () => $this->user()->isNot($this->route('user'))), 'nullable', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
            // Kosongkan jika tidak ingin mengganti kata sandi
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama', 'role' => 'peran', 'password' => 'kata sandi'];
    }
}
