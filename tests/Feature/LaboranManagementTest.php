<?php

use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createKepalaLab(): User
{
    return User::factory()->kepalaLab()->create([
        'identity_type' => 'pegawai',
        'institution' => 'Fakultas Teknik Unwahas',
    ]);
}

test('kepala lab can open the laboran management page and see lab column', function () {
    $kepala = createKepalaLab();
    $laboratory = Laboratory::factory()->create([
        'name' => 'Laboratorium Pengujian',
        'code' => 'LPJ',
    ]);
    $laboran = User::factory()->laboran()->create([
        'name' => 'Siti Laboran',
        'laboratory_id' => $laboratory->id,
    ]);

    $this
        ->actingAs($kepala)
        ->get(route('admin.laborans.index'))
        ->assertOk()
        ->assertViewHas('laborans')
        ->assertViewHas('laboratories')
        ->assertSee('Lab')
        ->assertSee('LPJ')
        ->assertSee('Laboratorium Pengujian')
        ->assertSee('Siti Laboran');
});

test('kepala lab can filter laborans by laboratory', function () {
    $kepala = createKepalaLab();
    $lab1 = Laboratory::factory()->create(['name' => 'Lab Otomasi', 'code' => 'LOTO']);
    $lab2 = Laboratory::factory()->create(['name' => 'Lab Fabrikasi', 'code' => 'LFAB']);

    $laboran1 = User::factory()->laboran()->create(['name' => 'Laboran Otomasi', 'laboratory_id' => $lab1->id]);
    $laboran2 = User::factory()->laboran()->create(['name' => 'Laboran Fabrikasi', 'laboratory_id' => $lab2->id]);

    $response = $this
        ->actingAs($kepala)
        ->get(route('admin.laborans.index', ['laboratory_id' => $lab1->id]))
        ->assertOk();

    $response->assertSee('Laboran Otomasi')
        ->assertDontSee('Laboran Fabrikasi');
});

test('kepala lab can create a laboran account with pegawai identity', function () {
    $kepala = createKepalaLab();
    $laboratory = Laboratory::factory()->create();

    $this
        ->actingAs($kepala)
        ->post(route('admin.laborans.store'), [
            'name' => 'Budi Santoso',
            'identity_number' => '199501012020121001',
            'email' => 'budi.laboran@unwahas.ac.id',
            'password' => 'rahasia123',
            'laboratory_id' => $laboratory->id,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $laboran = User::query()->where('email', 'budi.laboran@unwahas.ac.id')->firstOrFail();

    expect($laboran->name)->toBe('Budi Santoso')
        ->and($laboran->role)->toBe('laboran')
        ->and($laboran->identity_type)->toBe('pegawai')
        ->and($laboran->institution)->toBe('Fakultas Teknik Unwahas')
        ->and($laboran->laboratory_id)->toBe($laboratory->id)
        ->and($laboran->identity_number)->toBe('199501012020121001')
        ->and(Hash::check('rahasia123', $laboran->password))->toBeTrue();
});

test('laboran creation requires a unique nip and email', function () {
    $kepala = createKepalaLab();
    $laboratory = Laboratory::factory()->create();
    $existing = User::factory()->laboran()->create([
        'identity_number' => '199501012020121001',
        'email' => 'taken@unwahas.ac.id',
    ]);

    $this
        ->actingAs($kepala)
        ->post(route('admin.laborans.store'), [
            'name' => 'Duplikat Akun',
            'identity_number' => $existing->identity_number,
            'email' => $existing->email,
            'password' => 'rahasia123',
            'laboratory_id' => $laboratory->id,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['identity_number', 'email']);
});

test('laboran and klien cannot access the laboran management routes', function () {
    $laboratory = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create(['laboratory_id' => $laboratory->id]);
    $klien = User::factory()->klien()->create();

    $this->actingAs($laboran)->get(route('admin.laborans.index'))->assertForbidden();
    $this->actingAs($laboran)->post(route('admin.laborans.store'))->assertForbidden();
    $this->actingAs($klien)->get(route('admin.laborans.index'))->assertForbidden();
});

test('kepala lab can update a laboran laboratory assignment and reset the password', function () {
    $kepala = createKepalaLab();
    $oldLaboratory = Laboratory::factory()->create();
    $newLaboratory = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create([
        'name' => 'Nama Lama',
        'laboratory_id' => $oldLaboratory->id,
        'password' => 'password_lama',
    ]);

    $this
        ->actingAs($kepala)
        ->patch(route('admin.laborans.update', $laboran), [
            'name' => 'Nama Baru',
            'identity_number' => $laboran->identity_number ?? '199501012020121002',
            'email' => $laboran->email,
            'password' => 'password_baru',
            'laboratory_id' => $newLaboratory->id,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $laboran->refresh();

    expect($laboran->name)->toBe('Nama Baru')
        ->and($laboran->laboratory_id)->toBe($newLaboratory->id)
        ->and(Hash::check('password_baru', $laboran->password))->toBeTrue()
        ->and($laboran->role)->toBe('laboran');
});

test('kepala lab can update a laboran without changing the password', function () {
    $kepala = createKepalaLab();
    $laboratory = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create([
        'laboratory_id' => $laboratory->id,
        'password' => 'password_lama',
    ]);

    $this
        ->actingAs($kepala)
        ->patch(route('admin.laborans.update', $laboran), [
            'name' => 'Tanpa Reset Password',
            'identity_number' => $laboran->identity_number ?? '199501012020121003',
            'email' => $laboran->email,
            'password' => '',
            'laboratory_id' => $laboratory->id,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $laboran->refresh();

    expect($laboran->name)->toBe('Tanpa Reset Password')
        ->and(Hash::check('password_lama', $laboran->password))->toBeTrue();
});

test('kepala lab can delete a laboran account', function () {
    $kepala = createKepalaLab();
    $laboratory = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create(['laboratory_id' => $laboratory->id]);

    $this
        ->actingAs($kepala)
        ->delete(route('admin.laborans.destroy', $laboran))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $laboran->id]);
});

test('kepala lab cannot manage a non laboran account through the laboran routes', function () {
    $kepala = createKepalaLab();
    $otherKepala = User::factory()->kepalaLab()->create();

    $this
        ->actingAs($kepala)
        ->delete(route('admin.laborans.destroy', $otherKepala))
        ->assertNotFound();

    $this->assertDatabaseHas('users', ['id' => $otherKepala->id]);
});

test('kepala lab can toggle laboran active status', function () {
    $kepala = createKepalaLab();
    $laboratory = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create([
        'laboratory_id' => $laboratory->id,
        'is_active' => true,
    ]);

    // Deactivate
    $this
        ->actingAs($kepala)
        ->patch(route('admin.laborans.toggle-active', $laboran))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($laboran->refresh()->is_active)->toBeFalse();

    // Re-activate
    $this
        ->actingAs($kepala)
        ->patch(route('admin.laborans.toggle-active', $laboran))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($laboran->refresh()->is_active)->toBeTrue();
});

test('inactive user is blocked from logging in with an informative message', function () {
    $laboratory = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create([
        'laboratory_id' => $laboratory->id,
        'email' => 'inactive.laboran@example.com',
        'password' => Hash::make('password123'),
        'is_active' => false,
    ]);

    $response = $this->post('/login', [
        'email' => 'inactive.laboran@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Kepala Laboratorium.',
    ]);
    $this->assertGuest();
});

test('inactive user with active session is logged out by EnsureUserIsActive middleware', function () {
    $laboratory = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create([
        'laboratory_id' => $laboratory->id,
        'is_active' => false,
    ]);

    $response = $this
        ->actingAs($laboran)
        ->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
