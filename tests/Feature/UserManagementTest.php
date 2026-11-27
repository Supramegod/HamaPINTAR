<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
    }

    public function test_petugas_tidak_bisa_mengakses_manajemen_pengguna(): void
    {
        $operator = User::factory()->create();

        $this->actingAs($operator)->get(route('users.index'))->assertForbidden();
        $this->actingAs($operator)->get(route('users.create'))->assertForbidden();
        $this->actingAs($operator)->post(route('users.store'), [])->assertForbidden();
        $this->actingAs($operator)->delete(route('users.destroy', $this->admin))->assertForbidden();
    }

    public function test_admin_melihat_daftar_dan_mencari_pengguna(): void
    {
        User::factory()->create(['name' => 'Budi Santoso']);
        User::factory()->create(['name' => 'Siti Aminah']);

        $this->actingAs($this->admin)->get(route('users.index', ['q' => 'budi']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Aminah');
    }

    public function test_admin_menambah_pengguna(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Petugas Baru',
            'email' => 'baru@hamapintar.test',
            'role' => 'operator',
            'is_active' => '1',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('users.index'))->assertSessionHas('success');

        $user = User::where('email', 'baru@hamapintar.test')->firstOrFail();
        $this->assertSame(UserRole::Operator, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_validasi_tambah_pengguna(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => '',
            'email' => $this->admin->email,
            'role' => 'superuser',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }

    public function test_admin_mengubah_pengguna_tanpa_mengganti_kata_sandi(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;

        $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name' => 'Nama Baru',
            'email' => $user->email,
            'role' => 'admin',
            'is_active' => '0',
        ])->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame($oldHash, $user->password);
    }

    public function test_admin_tidak_bisa_menurunkan_peran_atau_menonaktifkan_diri_sendiri(): void
    {
        $this->actingAs($this->admin)->put(route('users.update', $this->admin), [
            'name' => 'Admin Ganti Nama',
            'email' => $this->admin->email,
            'role' => 'operator',
            'is_active' => '0',
        ])->assertRedirect(route('users.index'));

        $this->admin->refresh();
        $this->assertSame('Admin Ganti Nama', $this->admin->name);
        $this->assertSame(UserRole::Admin, $this->admin->role);
        $this->assertTrue($this->admin->is_active);
    }

    public function test_form_ubah_diri_sendiri_tanpa_field_peran_tetap_valid(): void
    {
        $this->actingAs($this->admin)->put(route('users.update', $this->admin), [
            'name' => 'Admin',
            'email' => $this->admin->email,
        ])->assertSessionHasNoErrors();
    }

    public function test_admin_menghapus_pengguna_lain_tetapi_tidak_dirinya_sendiri(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)->delete(route('users.destroy', $user))->assertRedirect(route('users.index'));
        $this->assertModelMissing($user);

        $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin))->assertForbidden();
        $this->assertModelExists($this->admin);
    }

    public function test_url_berbahasa_indonesia(): void
    {
        $this->assertStringEndsWith('/pengguna/tambah', route('users.create'));
        $this->assertStringEndsWith("/pengguna/{$this->admin->id}/ubah", route('users.edit', $this->admin));
    }
}
