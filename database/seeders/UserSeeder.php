<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun awal untuk development. GANTI password setelah login pertama.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(['email' => 'admin@hamapintar.test'], [
            'name' => 'Administrator',
            'password' => 'password',
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        User::query()->updateOrCreate(['email' => 'petugas@hamapintar.test'], [
            'name' => 'Petugas Lapangan',
            'password' => 'password',
            'role' => UserRole::Operator,
            'is_active' => true,
        ]);
    }
}
