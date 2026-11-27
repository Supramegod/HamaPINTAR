<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Hak akses berbasis peran
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('delete-detections', fn (User $user) => $user->isAdmin());

        // Admin tidak boleh menghapus / menonaktifkan / menurunkan peran dirinya sendiri
        Gate::define('modify-account-status', fn (User $actor, User $target) => $actor->isAdmin() && $actor->isNot($target));

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        Paginator::defaultView('components.pagination');

        // URL resource berbahasa Indonesia: /pengguna/tambah, /pengguna/{id}/ubah
        Route::resourceVerbs(['create' => 'tambah', 'edit' => 'ubah']);
    }
}
