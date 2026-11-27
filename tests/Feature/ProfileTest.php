<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_profil_tampil(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('Budi Santoso')->assertSee('BS');
    }

    public function test_memperbarui_profil(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), ['name' => 'Nama Baru', 'email' => 'baru@contoh.id'])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('baru@contoh.id', $user->fresh()->email);
    }

    public function test_ganti_kata_sandi_harus_dengan_kata_sandi_lama_yang_benar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'salah',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrorsIn('password', 'current_password');

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));
    }
}
