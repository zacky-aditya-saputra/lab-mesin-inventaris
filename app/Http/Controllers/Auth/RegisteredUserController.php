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
                ? ['required', 'numeric', 'digits:13']
                : ['required', 'string', 'max:30'],
            'phone_number' => ['required', 'string', 'max:20'],
            'institution' => ['required', 'string', 'max:255'],
            'identity_card_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
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
            'identity_card_path' => $request->file('identity_card_file')->store('identity_cards', 'public'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('catalog.index', absolute: false));
    }
}
