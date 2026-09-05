<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the public e-catalog with all active tools.
     */
    public function index(Request $request): View
    {
        $categoriesList = Category::query()
            ->orderBy('name')
            ->pluck('name')
            ->prepend('Semua Alat');

        $toolsList = Tool::query()
            ->with('category')
            ->where('is_active', true)
            ->get()
            ->map(fn (Tool $tool): array => [
                'id' => $tool->id,
                'code' => $tool->code,
                'name' => $tool->name,
                'slug' => $tool->slug,
                'category' => $tool->category?->name,
                'specification' => $tool->specification,
                'total_stock' => $tool->total_stock,
                'available_stock' => $tool->available_stock,
                'status' => $this->resolveAvailabilityStatus($tool->available_stock),
                'image' => $tool->image_path ? asset('storage/'.$tool->image_path) : null,
            ])
            ->all();

        return view('catalog.index', compact('categoriesList', 'toolsList'));
    }

    /**
     * Display the public detail page for the specified tool.
     */
    public function show(string $slug): View
    {
        $tool = Tool::query()
            ->with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('catalog.show', [
            'tool' => $tool,
            'availabilityStatus' => $this->resolveAvailabilityStatus($tool->available_stock),
        ]);
    }

    /**
     * Resolve the human-readable availability status from the available stock.
     */
    private function resolveAvailabilityStatus(int $availableStock): string
    {
        if ($availableStock > 3) {
            return 'Tersedia';
        }

        if ($availableStock > 0) {
            return 'Stok Terbatas';
        }

        return 'Tidak Tersedia';
    }
}
