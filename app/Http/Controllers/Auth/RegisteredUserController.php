<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'identity_type' => ['required', 'in:mahasiswa,umum'],
            'identity_number' => $request->input('identity_type') === 'umum'
                ? ['required', 'numeric', 'digits_between:13,16', 'unique:'.User::class]
                : ['required', 'string', 'max:30', 'unique:'.User::class],
            'phone_number' => ['required', 'string', 'max:20'],
            'institution' => ['required', 'string', 'max:255'],
            'identity_card_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok. Harap ulangi kata sandi dengan benar.',
            'identity_type.required' => 'Kategori peminjam wajib dipilih.',
            'identity_type.in' => 'Kategori peminjam harus Mahasiswa Unwahas atau Umum.',
            'identity_number.required' => 'Nomor identitas (NIM/NIK) wajib diisi.',
            'identity_number.numeric' => 'NIK untuk kategori umum harus berupa angka.',
            'identity_number.digits_between' => 'NIK untuk kategori umum harus terdiri dari 13 hingga 16 digit angka.',
            'identity_number.max' => 'Nomor identitas maksimal 30 karakter.',
            'identity_number.unique' => 'NIM atau NIK ini sudah terdaftar di sistem. Silakan periksa kembali atau masuk ke akun Anda.',
            'phone_number.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_number.max' => 'Nomor WhatsApp maksimal 20 digit.',
            'institution.required' => 'Program studi atau nama instansi wajib diisi.',
            'identity_card_file.required' => 'Berkas kartu identitas (KTM/KTP) wajib diunggah.',
            'identity_card_file.mimes' => 'Format kartu identitas harus JPG, JPEG, PNG, WEBP, atau PDF.',
            'identity_card_file.max' => 'Ukuran berkas kartu identitas tidak boleh melebihi 5MB.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'klien',
            'identity_type' => $request->identity_type,
            'identity_number' => $request->identity_number,
            'phone_number' => $request->phone_number,
            'institution' => $request->institution,
            'identity_card_path' => $request->file('identity_card_file')->store('identity_cards', 'local'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('catalog.index', absolute: false));
    }
}
