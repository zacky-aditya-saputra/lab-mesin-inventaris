<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Storage::fake('local');

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'identity_type' => 'mahasiswa',
        'identity_number' => '32602100099',
        'phone_number' => '081234567899',
        'institution' => 'Teknik Mesin S1',
        'identity_card_file' => UploadedFile::fake()->create('kartu-mahasiswa.pdf', 200),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('catalog.index', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->sole();

    expect($user->role)->toBe('klien')
        ->and($user->identity_type)->toBe('mahasiswa')
        ->and($user->identity_number)->toBe('32602100099')
        ->and($user->phone_number)->toBe('081234567899')
        ->and($user->institution)->toBe('Teknik Mesin S1')
        ->and($user->identity_card_path)->toStartWith('identity_cards/');

    Storage::disk('local')->assertExists($user->identity_card_path);
    Storage::disk('public')->assertMissing($user->identity_card_path);
});

test('registration is rejected without an identity card file', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'identity_type' => 'mahasiswa',
        'identity_number' => '32602100098',
        'phone_number' => '081234567898',
        'institution' => 'Teknik Mesin S1',
    ]);

    $response->assertSessionHasErrors('identity_card_file');
    $this->assertGuest();
});

test('general users (umum) can register with NIK and valid image', function () {
    Storage::fake('local');

    $response = $this->post('/register', [
        'name' => 'Mitra Industri',
        'email' => 'mitra@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'identity_type' => 'umum',
        'identity_number' => '3374012345670001',
        'phone_number' => '085712345678',
        'institution' => 'PT Manufaktur Presisi Semarang',
        'identity_card_file' => UploadedFile::fake()->image('ktp.jpg', 800, 600),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('catalog.index', absolute: false));

    $user = User::query()->where('email', 'mitra@example.com')->sole();

    expect($user->role)->toBe('klien')
        ->and($user->identity_type)->toBe('umum')
        ->and($user->identity_number)->toBe('3374012345670001')
        ->and($user->institution)->toBe('PT Manufaktur Presisi Semarang');

    Storage::disk('local')->assertExists($user->identity_card_path);
});

test('registration rejects duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/register', [
        'name' => 'Duplicate User',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'identity_type' => 'mahasiswa',
        'identity_number' => '32602100055',
        'phone_number' => '081234567855',
        'institution' => 'Teknik Mesin S1',
        'identity_card_file' => UploadedFile::fake()->create('ktm.pdf', 100),
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration rejects password confirmation mismatch', function () {
    $response = $this->post('/register', [
        'name' => 'Mismatch User',
        'email' => 'mismatch@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password',
        'identity_type' => 'mahasiswa',
        'identity_number' => '32602100056',
        'phone_number' => '081234567856',
        'institution' => 'Teknik Mesin S1',
        'identity_card_file' => UploadedFile::fake()->create('ktm.pdf', 100),
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration rejects duplicate identity_number for mahasiswa with clear error message', function () {
    User::factory()->create(['identity_number' => '24103041027']);

    $response = $this->post('/register', [
        'name' => 'Duplicate NIM User',
        'email' => 'newstudent@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'identity_type' => 'mahasiswa',
        'identity_number' => '24103041027',
        'phone_number' => '081234567800',
        'institution' => 'Teknik Mesin S1',
        'identity_card_file' => UploadedFile::fake()->create('ktm.pdf', 100),
    ]);

    $response->assertSessionHasErrors([
        'identity_number' => 'NIM atau NIK ini sudah terdaftar di sistem. Silakan periksa kembali atau masuk ke akun Anda.',
    ]);
    $this->assertGuest();
});

test('registration rejects duplicate identity_number for umum category', function () {
    User::factory()->create(['identity_number' => '3374012345670001']);

    $response = $this->post('/register', [
        'name' => 'Duplicate NIK User',
        'email' => 'newclient@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'identity_type' => 'umum',
        'identity_number' => '3374012345670001',
        'phone_number' => '081234567801',
        'institution' => 'Mitra Industri',
        'identity_card_file' => UploadedFile::fake()->create('ktp.jpg', 100),
    ]);

    $response->assertSessionHasErrors('identity_number');
    $this->assertGuest();
});

test('user roleLabel returns correct label and is rendered in top right profile', function () {
    $umumUser = User::factory()->create([
        'name' => 'Budi Santoso Umum',
        'role' => 'klien',
        'identity_type' => 'umum',
    ]);
    expect($umumUser->roleLabel())->toBe('Umum');

    $mhsUser = User::factory()->create([
        'name' => 'Zacky Mahasiswa',
        'role' => 'klien',
        'identity_type' => 'mahasiswa',
    ]);
    expect($mhsUser->roleLabel())->toBe('Mahasiswa');

    $responseUmum = $this->actingAs($umumUser)->get(route('catalog.index'));
    $responseUmum->assertOk();
    $responseUmum->assertSee('Umum');
    $responseUmum->assertDontSee('text-blue-600">Mahasiswa</p>', false);

    $responseMhs = $this->actingAs($mhsUser)->get(route('catalog.index'));
    $responseMhs->assertOk();
    $responseMhs->assertSee('Mahasiswa');
});
