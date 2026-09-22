<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreToolRequest;
use App\Http\Requests\Admin\UpdateToolRequest;
use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ToolController extends Controller
{
    /**
     * Display a listing of the tools, scoped to the user's laboratory role.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $tools = Tool::query()
            ->with(['category', 'laboratory'])
            ->when($user->isLaboran(), fn ($query) => $query->where('laboratory_id', $user->laboratory_id))
            ->when($user->isKepalaLab() && $request->filled('laboratory_id'), fn ($query) => $query->where('laboratory_id', $request->integer('laboratory_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.tools.index', [
            'tools' => $tools,
            'categories' => Category::query()->orderBy('name')->get(),
            'laboratories' => Laboratory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Generate the next inventory code for the given laboratory and category.
     *
     * Format: LABTM{labId}-{categoryCode}-{MMYY}-{NNN}
     */
    public function generateCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'laboratory_id' => ['required', 'integer', 'exists:laboratories,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $categoryCode = strtoupper(Category::query()->findOrFail($validated['category_id'])->code_or_default);

        $prefix = sprintf(
            'LABTM%d-%s-%s-',
            (int) $validated['laboratory_id'],
            $categoryCode,
            now()->format('my'),
        );

        $lastTool = Tool::withTrashed()
            ->where('code', 'LIKE', $prefix.'%')
            ->orderByDesc('code')
            ->first();

        $nextNum = $lastTool
            ? ((int) substr((string) $lastTool->code, -3)) + 1
            : 1;

        return response()->json([
            'code' => $prefix.str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Show the form for creating a new tool.
     */
    public function create(): View
    {
        return view('admin.tools.create', [
            'categories' => Category::query()->orderBy('name')->get(),
            'laboratories' => Laboratory::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created tool in storage.
     */
    public function store(StoreToolRequest $request): RedirectResponse
    {
        Tool::create([
            'category_id' => $request->category_id,
            'laboratory_id' => $request->laboratory_id,
            'code' => $request->code,
            'name' => $request->name,
            'slug' => Str::slug($request->name.' '.$request->code),
            'specification' => $request->specification,
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('tools', 'public')
                : null,
            'total_stock' => $request->total_stock,
            'available_stock' => $request->total_stock,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.tools.index')->with('success', 'Alat berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified tool.
     */
    public function edit(Tool $tool): View
    {
        $this->authorizeToolLaboratory($tool);

        return view('admin.tools.edit', [
            'tool' => $tool,
            'categories' => Category::query()->orderBy('name')->get(),
            'laboratories' => Laboratory::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified tool in storage.
     */
    public function update(UpdateToolRequest $request, Tool $tool): RedirectResponse
    {
        $this->authorizeToolLaboratory($tool);

        $stockDiff = (int) $request->total_stock - $tool->total_stock;
        $newAvailableStock = $tool->available_stock + $stockDiff;

        if ($newAvailableStock < 0) {
            return back()
                ->withInput()
                ->withErrors(['total_stock' => 'Total stok tidak boleh lebih kecil dari jumlah alat yang sedang dipinjam saat ini.']);
        }

        if ($request->hasFile('image')) {
            if ($tool->image_path) {
                Storage::disk('public')->delete($tool->image_path);
            }

            $tool->image_path = $request->file('image')->store('tools', 'public');
        }

        $tool->update([
            'category_id' => $request->category_id,
            'laboratory_id' => $request->laboratory_id,
            'code' => $request->code,
            'name' => $request->name,
            'slug' => Str::slug($request->name.' '.$request->code),
            'specification' => $request->specification,
            'image_path' => $tool->image_path,
            'total_stock' => $request->total_stock,
            'available_stock' => $newAvailableStock,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.tools.index')->with('success', 'Alat berhasil diperbarui.');
    }

    /**
     * Remove the specified tool from storage.
     */
    public function destroy(Tool $tool): RedirectResponse
    {
        $tool->delete();

        return redirect()->route('admin.tools.index')->with('success', 'Alat berhasil dihapus.');
    }

    /**
     * Ensure laborans may only manage tools belonging to their own laboratory.
     */
    private function authorizeToolLaboratory(Tool $tool): void
    {
        $user = auth()->user();

        if ($user->isLaboran() && $tool->laboratory_id !== $user->laboratory_id) {
            abort(403, 'Anda tidak memiliki wewenang atas alat di luar laboratorium Anda.');
        }
    }
}
