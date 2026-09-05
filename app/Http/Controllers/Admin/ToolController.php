<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreToolRequest;
use App\Http\Requests\Admin\UpdateToolRequest;
use App\Models\Category;
use App\Models\Tool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ToolController extends Controller
{
    /**
     * Display a listing of the tools.
     */
    public function index(): View
    {
        $tools = Tool::query()
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('admin.tools.index', compact('tools'));
    }

    /**
     * Show the form for creating a new tool.
     */
    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.tools.create', compact('categories'));
    }

    /**
     * Store a newly created tool in storage.
     */
    public function store(StoreToolRequest $request): RedirectResponse
    {
        Tool::create([
            'category_id' => $request->category_id,
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
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.tools.edit', compact('tool', 'categories'));
    }

    /**
     * Update the specified tool in storage.
     */
    public function update(UpdateToolRequest $request, Tool $tool): RedirectResponse
    {
        if ($request->hasFile('image')) {
            if ($tool->image_path) {
                Storage::disk('public')->delete($tool->image_path);
            }

            $tool->image_path = $request->file('image')->store('tools', 'public');
        }

        $tool->update([
            'category_id' => $request->category_id,
            'code' => $request->code,
            'name' => $request->name,
            'slug' => Str::slug($request->name.' '.$request->code),
            'specification' => $request->specification,
            'image_path' => $tool->image_path,
            'total_stock' => $request->total_stock,
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
}
