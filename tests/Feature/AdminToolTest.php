<?php

use App\Models\Category;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin tool image upload is stored on the public disk', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this
        ->actingAs($admin)
        ->post(route('admin.tools.store'), [
            'category_id' => $category->id,
            'code' => 'LAB-TEST-001',
            'name' => 'Alat Uji Unggah Gambar',
            'specification' => 'Spesifikasi alat uji.',
            'total_stock' => 5,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('alat.jpg'),
        ])
        ->assertRedirect(route('admin.tools.index'));

    $tool = Tool::query()->where('code', 'LAB-TEST-001')->firstOrFail();

    $this->assertNotNull($tool->image_path);
    $this->assertStringStartsWith('tools/', $tool->image_path);
    $this->assertSame(5, $tool->available_stock);

    Storage::disk('public')->assertExists($tool->image_path);
});

test('admin tool image replacement deletes the old file and stores the new one', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $oldImagePath = UploadedFile::fake()->image('lama.jpg')->store('tools', 'public');
    $tool = Tool::factory()->for($category)->create(['image_path' => $oldImagePath]);

    Storage::disk('public')->assertExists($oldImagePath);

    $this
        ->actingAs($admin)
        ->patch(route('admin.tools.update', $tool), [
            'category_id' => $category->id,
            'code' => $tool->code,
            'name' => $tool->name,
            'specification' => $tool->specification,
            'total_stock' => $tool->total_stock,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('baru.jpg'),
        ])
        ->assertRedirect(route('admin.tools.index'));

    $tool->refresh();

    Storage::disk('public')->assertMissing($oldImagePath);
    $this->assertNotSame($oldImagePath, $tool->image_path);
    Storage::disk('public')->assertExists($tool->image_path);
});

test('students are forbidden from accessing the create tool form', function () {
    $student = User::factory()->student()->create();

    $this
        ->actingAs($student)
        ->get(route('admin.tools.create'))
        ->assertForbidden();
});

test('guests are redirected to login when accessing the create tool form', function () {
    $this
        ->get(route('admin.tools.create'))
        ->assertRedirect(route('login'));
});
