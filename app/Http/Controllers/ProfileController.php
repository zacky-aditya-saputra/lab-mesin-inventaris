<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['identity_card_file', 'role', 'laboratory_id', 'identity_type']);

        if ($request->user()->isLaboran() || $request->user()->isKepalaLab()) {
            $data['identity_type'] = 'pegawai';
        } elseif ($request->filled('identity_type')) {
            $data['identity_type'] = $request->input('identity_type');
        }

        if ($request->hasFile('identity_card_file')) {
            if ($request->user()->identity_card_path) {
                Storage::disk('local')->delete($request->user()->identity_card_path);
                Storage::disk('public')->delete($request->user()->identity_card_path);
            }

            $data['identity_card_path'] = $request->file('identity_card_file')->store('identity_cards', 'local');
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
