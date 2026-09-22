<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Support\Str;

test('admin can store a category with a code that is saved uppercase', function () {
    $admin = User::factory()->kepalaLab()->create();

    $this
        ->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Alat Ukur Presisi',
            'code' => 'UK',
            'description' => 'Peralatan ukur presisi.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $category = Category::query()->where('slug', 'alat-ukur-presisi')->firstOrFail();

    $this->assertSame('UK', $category->code);
});

test('category code is required', function () {
    $admin = User::factory()->kepalaLab()->create();

    $this
        ->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Mesin Perkakas',
            'code' => '',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['code']);

    $this->assertDatabaseMissing('categories', ['slug' => 'mesin-perkakas']);
});

test('category code must be uppercase', function () {
    $admin = User::factory()->kepalaLab()->create();

    $this
        ->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Elektronika',
            'code' => 'el',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['code']);

    $this->assertDatabaseMissing('categories', ['slug' => 'elektronika']);
});

test('category code must be unique', function () {
    $admin = User::factory()->kepalaLab()->create();
    Category::factory()->create(['name' => 'Mesin Perkakas', 'code' => 'MS']);

    $this
        ->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Uji Material',
            'code' => 'MS',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['code']);
});

test('category code must not exceed ten characters', function () {
    $admin = User::factory()->kepalaLab()->create();

    $this
        ->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Kategori Kode Panjang',
            'code' => Str::repeat('X', 11),
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['code']);
});

test('admin can update the code of an existing category', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create(['name' => 'Alat Ukur Presisi', 'code' => 'AUP']);

    $this
        ->actingAs($admin)
        ->patch(route('admin.categories.update', $category), [
            'name' => 'Alat Ukur Presisi',
            'code' => 'MS',
            'description' => $category->description,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $category->refresh();

    $this->assertSame('MS', $category->code);
});

test('category holding tools cannot be deleted and the guard message is returned', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create();
    Tool::factory()->for($category)->for(Laboratory::factory())->create();

    $this
        ->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('error', 'Kategori tidak dapat dihapus karena masih memuat alat inventaris. Pindahkan atau hapus alatnya terlebih dahulu.');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('category without tools can be deleted', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create();

    $this
        ->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('category code accessor falls back to uppercase name initials', function () {
    $withCode = Category::factory()->make(['name' => 'Alat Ukur Presisi', 'code' => 'UK']);
    $withoutCode = Category::factory()->make(['name' => 'Alat Ukur Presisi', 'code' => null]);
    $singleWord = Category::factory()->make(['name' => 'Kompresor', 'code' => null]);

    $this->assertSame('UK', $withCode->code_or_default);
    $this->assertSame('AUP', $withoutCode->code_or_default);
    $this->assertSame('KMP', $singleWord->code_or_default);
});
