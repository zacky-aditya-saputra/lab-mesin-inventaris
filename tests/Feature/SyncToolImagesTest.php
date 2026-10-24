<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->toolsDir = storage_path('app/public/tools');

    if (! File::isDirectory($this->toolsDir)) {
        File::makeDirectory($this->toolsDir, 0755, true);
    }
});

test('sync tool images command scans directory and matches images by code or name', function () {
    $lab = Laboratory::factory()->create();
    $cat = Category::factory()->create();

    $tool1 = Tool::factory()->create([
        'laboratory_id' => $lab->id,
        'category_id' => $cat->id,
        'code' => 'LABTM99-TS-1026-001',
        'name' => 'Mesin Bubut Presisi Tinggi',
        'slug' => 'mesin-bubut-presisi-tinggi',
        'image_path' => null,
    ]);

    $tool2 = Tool::factory()->create([
        'laboratory_id' => $lab->id,
        'category_id' => $cat->id,
        'code' => 'LABTM99-TS-1026-002',
        'name' => 'Jangka Sorong Mitutoyo Digital',
        'slug' => 'jangka-sorong-mitutoyo-digital',
        'image_path' => null,
    ]);

    // Buat file dummy di storage/app/public/tools
    $file1 = $this->toolsDir.'/LABTM99-TS-1026-001.jpg';
    $file2 = $this->toolsDir.'/jangka-sorong.png';
    File::put($file1, 'dummy-content-1');
    File::put($file2, 'dummy-content-2');

    try {
        $this->artisan('tools:sync-images')
            ->assertSuccessful();

        $tool1->refresh();
        $tool2->refresh();

        expect($tool1->image_path)->toBe('tools/LABTM99-TS-1026-001.jpg')
            ->and($tool2->image_path)->toBe('tools/jangka-sorong.png');
    } finally {
        if (File::exists($file1)) {
            File::delete($file1);
        }
        if (File::exists($file2)) {
            File::delete($file2);
        }
    }
});

test('sync tool images supports dry-run option without modifying database', function () {
    $lab = Laboratory::factory()->create();
    $cat = Category::factory()->create();

    $tool = Tool::factory()->create([
        'laboratory_id' => $lab->id,
        'category_id' => $cat->id,
        'code' => 'LABTM99-TS-1026-003',
        'name' => 'Mikrometer Digital Sekrup',
        'slug' => 'mikrometer-digital-sekrup',
        'image_path' => null,
    ]);

    $file = $this->toolsDir.'/LABTM99-TS-1026-003.webp';
    File::put($file, 'dummy-content');

    try {
        $this->artisan('tools:sync-images --dry-run')
            ->expectsOutputToContain('[DRY-RUN AKTIF]')
            ->assertSuccessful();

        expect($tool->fresh()->image_path)->toBeNull();
    } finally {
        if (File::exists($file)) {
            File::delete($file);
        }
    }
});
