<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaboratoryController extends Controller
{
    /**
     * Display a listing of all laboratories (only accessible by kepala lab).
     */
    public function index(): View
    {
        $laboratories = Laboratory::query()
            ->withCount(['tools', 'users' => fn ($q) => $q->where('role', 'laboran'), 'loanRequests'])
            ->orderBy('name')
            ->get();

        return view('admin.laboratories.index', compact('laboratories'));
    }

    /**
     * Store a newly created laboratory.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'alpha_num', 'unique:laboratories,code'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Nama laboratorium wajib diisi.',
            'code.required' => 'Kode laboratorium wajib diisi.',
            'code.unique' => 'Kode laboratorium sudah terdaftar. Gunakan kode lain.',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Laboratory::query()->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        Laboratory::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'slug' => $slug,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Laboratorium {$validated['name']} ({$validated['code']}) berhasil ditambahkan.");
    }

    /**
     * Update the specified laboratory.
     */
    public function update(Request $request, Laboratory $laboratory): RedirectResponse
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'alpha_num', Rule::unique('laboratories', 'code')->ignore($laboratory->id)],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Nama laboratorium wajib diisi.',
            'code.required' => 'Kode laboratorium wajib diisi.',
            'code.unique' => 'Kode laboratorium sudah terdaftar.',
        ]);

        $laboratory->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', $laboratory->is_active),
        ]);

        return back()->with('success', "Data laboratorium {$laboratory->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified laboratory from storage with relation safeguard.
     */
    public function destroy(Laboratory $laboratory): RedirectResponse
    {
        if ($laboratory->tools()->exists() || $laboratory->users()->exists() || $laboratory->loanRequests()->exists()) {
            return back()->with('error', "Laboratorium {$laboratory->name} tidak dapat dihapus karena masih memiliki relasi aktif (alat, staf laboran, atau riwayat tiket). Anda dapat menonaktifkannya.");
        }

        $name = $laboratory->name;
        $laboratory->delete();

        return back()->with('success', "Laboratorium {$name} berhasil dihapus.");
    }

    /**
     * Toggle the active status of the specified laboratory.
     */
    public function toggleActive(Laboratory $laboratory): RedirectResponse
    {
        $laboratory->update(['is_active' => ! $laboratory->is_active]);

        $status = $laboratory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status laboratorium {$laboratory->name} berhasil {$status}.");
    }
}
