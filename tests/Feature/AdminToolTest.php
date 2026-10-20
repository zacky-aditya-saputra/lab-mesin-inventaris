<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\StockAdjustment;
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

    $this->assertDatabaseHas('stock_adjustments', [
        'tool_id' => $tool->id,
        'user_id' => $admin->id,
        'type' => 'manual_adjustment',
        'delta' => 5,
        'before_available' => 6,
        'after_available' => 11,
    ]);
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

test('laboran can mark damaged tool stock as repaired', function () {
    $laboratory = Laboratory::factory()->create();
    $admin = User::factory()->laboran()->create(['laboratory_id' => $laboratory->id]);
    $category = Category::factory()->create();

    $tool = Tool::factory()->for($category)->for($laboratory)->create([
        'total_stock' => 10,
        'available_stock' => 5,
        'damaged_stock' => 3,
    ]);

    $this
        ->actingAs($admin)
        ->post(route('admin.tools.repair', $tool), [
            'quantity' => 2,
            'notes' => 'Selesai perbaikan dinamo dan pengujian kelistrikan',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $tool->refresh();
    expect($tool->damaged_stock)->toBe(1)
        ->and($tool->available_stock)->toBe(7)
        ->and($tool->total_stock)->toBe(10);

    $this->assertDatabaseHas('stock_adjustments', [
        'tool_id' => $tool->id,
        'user_id' => $admin->id,
        'type' => 'repair',
        'delta' => 2,
        'before_available' => 5,
        'after_available' => 7,
        'before_damaged' => 3,
        'after_damaged' => 1,
        'reason' => 'Selesai perbaikan dinamo dan pengujian kelistrikan',
    ]);
});

test('repair fails if quantity exceeds current damaged stock', function () {
    $laboratory = Laboratory::factory()->create();
    $admin = User::factory()->laboran()->create(['laboratory_id' => $laboratory->id]);
    $category = Category::factory()->create();

    $tool = Tool::factory()->for($category)->for($laboratory)->create([
        'total_stock' => 5,
        'available_stock' => 3,
        'damaged_stock' => 2,
    ]);

    $this
        ->actingAs($admin)
        ->post(route('admin.tools.repair', $tool), [
            'quantity' => 5,
            'notes' => 'Melebihi kapasitas rusak',
        ])
        ->assertSessionHasErrors('quantity');
});

test('laboran can view stock adjustments audit trail scoped to their laboratory', function () {
    $lab1 = Laboratory::factory()->create(['name' => 'Lab Permesinan']);
    $lab2 = Laboratory::factory()->create(['name' => 'Lab Material']);

    $laboran1 = User::factory()->laboran()->create(['laboratory_id' => $lab1->id]);
    $category = Category::factory()->create();

    $toolLab1 = Tool::factory()->for($category)->for($lab1)->create(['name' => 'Mesin Bubut Lab 1', 'damaged_stock' => 2]);
    $toolLab2 = Tool::factory()->for($category)->for($lab2)->create(['name' => 'Mesin Uji Tarik Lab 2', 'damaged_stock' => 1]);

    // Create adjustment in Lab 1
    StockAdjustment::create([
        'tool_id' => $toolLab1->id,
        'user_id' => $laboran1->id,
        'type' => 'repair',
        'delta' => 1,
        'before_available' => 3,
        'after_available' => 4,
        'before_damaged' => 2,
        'after_damaged' => 1,
        'reason' => 'Perbaikan bearing Lab 1',
        'created_at' => now(),
    ]);

    // Create adjustment in Lab 2
    StockAdjustment::create([
        'tool_id' => $toolLab2->id,
        'user_id' => $laboran1->id,
        'type' => 'repair',
        'delta' => 1,
        'before_available' => 1,
        'after_available' => 2,
        'before_damaged' => 1,
        'after_damaged' => 0,
        'reason' => 'Perbaikan sensor Lab 2',
        'created_at' => now(),
    ]);

    $response = $this
        ->actingAs($laboran1)
        ->get(route('admin.tools.adjustments'));

    $response->assertOk();
    $response->assertSee('Mesin Bubut Lab 1');
    $response->assertSee('Perbaikan bearing Lab 1');
    $response->assertDontSee('Mesin Uji Tarik Lab 2');
    $response->assertDontSee('Perbaikan sensor Lab 2');
});

test('kepala lab can view stock adjustments from all laboratories and filter by laboratory', function () {
    $lab1 = Laboratory::factory()->create(['name' => 'Lab A']);
    $lab2 = Laboratory::factory()->create(['name' => 'Lab B']);

    $kepalaLab = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create();

    $tool1 = Tool::factory()->for($category)->for($lab1)->create(['name' => 'Tool Lab A']);
    $tool2 = Tool::factory()->for($category)->for($lab2)->create(['name' => 'Tool Lab B']);

    StockAdjustment::create([
        'tool_id' => $tool1->id,
        'user_id' => $kepalaLab->id,
        'type' => 'manual_adjustment',
        'delta' => 3,
        'before_available' => 5,
        'after_available' => 8,
        'before_damaged' => 0,
        'after_damaged' => 0,
        'reason' => 'Pengadaan unit baru Lab A',
        'created_at' => now(),
    ]);

    StockAdjustment::create([
        'tool_id' => $tool2->id,
        'user_id' => $kepalaLab->id,
        'type' => 'repair',
        'delta' => 1,
        'before_available' => 2,
        'after_available' => 3,
        'before_damaged' => 1,
        'after_damaged' => 0,
        'reason' => 'Perbaikan unit Lab B',
        'created_at' => now(),
    ]);

    // View all
    $responseAll = $this
        ->actingAs($kepalaLab)
        ->get(route('admin.tools.adjustments'));

    $responseAll->assertOk();
    $responseAll->assertSee('Tool Lab A');
    $responseAll->assertSee('Tool Lab B');

    // Filter by Lab B
    $responseLabB = $this
        ->actingAs($kepalaLab)
        ->get(route('admin.tools.adjustments', ['laboratory_id' => $lab2->id]));

    $responseLabB->assertOk();
    $responseLabB->assertSee('Tool Lab B');
    $responseLabB->assertDontSee('Tool Lab A');
});

test('client is forbidden from accessing stock adjustments audit trail', function () {
    $client = User::factory()->student()->create();

    $this
        ->actingAs($client)
        ->get(route('admin.tools.adjustments'))
        ->assertForbidden();
});
