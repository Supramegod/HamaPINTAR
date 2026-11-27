<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Operator => 'Petugas',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Akses penuh, termasuk kelola pengguna dan hapus data.',
            self::Operator => 'Melihat seluruh halaman dan mengunggah data alat.',
        };
    }

    /** @return array<string, string> value => label, untuk select di form */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $role) => [$role->value => $role->label()])->all();
    }
}
