<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaboranController extends Controller
{
    /**
     * Display the laboran management page (only accessible by kepala lab).
     */
    public function index(Request $request): View
    {
        return view('admin.laborans.index', [
            'laborans' => User::query()
                ->where('role', 'laboran')
                ->with('laboratory')
                ->when($request->filled('laboratory_id'), fn ($query) => $query->where('laboratory_id', $request->integer('laboratory_id')))
                ->latest()
                ->get(),
            'laboratories' => Laboratory::query()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    /**
     * Store a newly created laboran account assigned to a laboratory.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedLaboran($request);

        User::create([
            'name' => $validated['name'],
            'identity_number' => $validated['identity_number'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'laboratory_id' => (int) $validated['laboratory_id'],
            'role' => 'laboran',
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        return back()->with('success', 'Akun laboran baru berhasil dibuat.');
    }

    /**
     * Update the specified laboran: profile data, laboratory assignment, or password reset.
     */
    public function update(Request $request, User $laboran): RedirectResponse
    {
        $this->authorizeLaboran($laboran);

        $validated = $this->validatedLaboran($request, $laboran);

        $laboran->fill([
            'name' => $validated['name'],
            'identity_number' => $validated['identity_number'],
            'email' => $validated['email'],
            'laboratory_id' => (int) $validated['laboratory_id'],
        ]);

        if (filled($validated['password'] ?? null)) {
            $laboran->password = $validated['password'];
        }

        $laboran->save();

        return back()->with('success', 'Data laboran berhasil diperbarui.');
    }

    /**
     * Remove the specified laboran account.
     */
    public function destroy(User $laboran): RedirectResponse
    {
        $this->authorizeLaboran($laboran);

        if ($laboran->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $laboran->delete();

        return back()->with('success', 'Akun laboran berhasil dihapus.');
    }

    /**
     * Toggle the active status of the specified laboran account (FR-KPL-03).
     */
    public function toggleActive(User $laboran): RedirectResponse
    {
        $this->authorizeLaboran($laboran);

        if ($laboran->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $laboran->update(['is_active' => ! $laboran->is_active]);

        $statusText = $laboran->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun laboran {$laboran->name} berhasil {$statusText}.");
    }

    /**
     * Ensure the target user is actually a laboran account.
     */
    private function authorizeLaboran(User $laboran): void
    {
        if ($laboran->role !== 'laboran') {
            abort(404, 'Akun laboran tidak ditemukan.');
        }
    }

    /**
     * Validate the laboran payload for both creation and update.
     *
     * @return array{name: string, identity_number: string, email: string, password?: string|null, laboratory_id: int}
     */
    private function validatedLaboran(Request $request, ?User $laboran = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'identity_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'identity_number')->ignore($laboran?->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($laboran?->id),
            ],
            'password' => [
                $laboran === null ? 'required' : 'nullable',
                'string',
                'min:8',
            ],
            'laboratory_id' => ['required', 'integer', 'exists:laboratories,id'],
        ]);
    }
}
