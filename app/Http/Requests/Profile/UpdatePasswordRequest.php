<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    /** Error ditampung di bag terpisah agar tidak tercampur dengan form profil. */
    protected $errorBag = 'password';

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['current_password' => 'kata sandi saat ini', 'password' => 'kata sandi baru'];
    }
}
