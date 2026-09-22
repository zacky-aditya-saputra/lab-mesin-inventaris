<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin tool image upload is stored on the public disk', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $laboratory = Laboratory::factory()->create();

    $this
        ->actingAs($admin)
        ->post(route('admin.tools.store'), [
            'laboratory_id' => $laboratory->id,
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
    $this->assertSame($laboratory->id, $tool->laboratory_id);

    Storage::disk('public')->assertExists($tool->image_path);
});

test('admin tool image replacement deletes the old file and stores the new one', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $laboratory = Laboratory::factory()->create();

    $oldImagePath = UploadedFile::fake()->image('lama.jpg')->store('tools', 'public');
    $tool = Tool::factory()->for($category)->for($laboratory)->create(['image_path' => $oldImagePath]);

    Storage::disk('public')->assertExists($oldImagePath);

    $this
        ->actingAs($admin)
        ->patch(route('admin.tools.update', $tool), [
            'laboratory_id' => $laboratory->id,
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

test('admin updating total stock correctly syncs available stock', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $laboratory = Laboratory::factory()->create();

    // Tool has 10 total, 6 available (4 currently borrowed)
    $tool = Tool::factory()->for($category)->for($laboratory)->create([
        'total_stock' => 10,
        'available_stock' => 6,
    ]);

    $this
        ->actingAs($admin)
        ->patch(route('admin.tools.update', $tool), [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
            'code' => $tool->code,
            'name' => $tool->name,
            'specification' => $tool->specification,
            'total_stock' => 15,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.tools.index'));

    $tool->refresh();
    $this->assertSame(15, $tool->total_stock);
    $this->assertSame(11, $tool->available_stock);
});

test('admin cannot reduce total stock below currently borrowed quantity', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $laboratory = Laboratory::factory()->create();

    // Tool has 10 total, 3 available (7 currently borrowed)
    $tool = Tool::factory()->for($category)->for($laboratory)->create([
        'total_stock' => 10,
        'available_stock' => 3,
    ]);

    // Attempting to lower total_stock to 5 would make available_stock = 3 + (5 - 10) = -2 < 0
    $this
        ->actingAs($admin)
        ->from(route('admin.tools.edit', $tool))
        ->patch(route('admin.tools.update', $tool), [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
            'code' => $tool->code,
            'name' => $tool->name,
            'specification' => $tool->specification,
            'total_stock' => 5,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.tools.edit', $tool))
        ->assertSessionHasErrors(['total_stock' => 'Total stok tidak boleh lebih kecil dari jumlah alat yang sedang dipinjam saat ini.']);

    $tool->refresh();
    $this->assertSame(10, $tool->total_stock);
    $this->assertSame(3, $tool->available_stock);
});
