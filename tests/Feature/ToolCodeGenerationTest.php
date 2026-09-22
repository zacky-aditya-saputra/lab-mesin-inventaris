<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use App\Models\User;

test('generate code returns the formatted code with sequence 001 when no tools exist', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create(['code' => 'UK']);
    $laboratory = Laboratory::factory()->create();

    $expectedPrefix = sprintf('LABTM%d-UK-%s-', $laboratory->id, now()->format('my'));

    $this
        ->actingAs($admin)
        ->get(route('admin.tools.generate-code', [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
        ]))
        ->assertOk()
        ->assertJson(['code' => $expectedPrefix.'001']);
});

test('generate code increments the sequence based on the last tool code', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create(['code' => 'UK']);
    $laboratory = Laboratory::factory()->create();

    $prefix = sprintf('LABTM%d-UK-%s-', $laboratory->id, now()->format('my'));

    Tool::factory()->for($category)->for($laboratory)->create(['code' => $prefix.'007']);

    $this
        ->actingAs($admin)
        ->get(route('admin.tools.generate-code', [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
        ]))
        ->assertOk()
        ->assertJson(['code' => $prefix.'008']);
});

test('generate code counts soft deleted tools to avoid duplicate codes', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create(['code' => 'UK']);
    $laboratory = Laboratory::factory()->create();

    $prefix = sprintf('LABTM%d-UK-%s-', $laboratory->id, now()->format('my'));

    Tool::factory()->for($category)->for($laboratory)->create(['code' => $prefix.'002'])->delete();

    $this
        ->actingAs($admin)
        ->get(route('admin.tools.generate-code', [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
        ]))
        ->assertOk()
        ->assertJson(['code' => $prefix.'003']);
});

test('generate code sequences are scoped per laboratory and category', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create(['code' => 'UK']);
    $otherCategory = Category::factory()->create(['code' => 'MS']);
    $laboratory = Laboratory::factory()->create();
    $otherLaboratory = Laboratory::factory()->create();

    $prefix = sprintf('LABTM%d-UK-%s-', $laboratory->id, now()->format('my'));
    Tool::factory()->for($category)->for($laboratory)->create(['code' => $prefix.'005']);
    Tool::factory()->for($category)->for($otherLaboratory)->create(['code' => sprintf('LABTM%d-UK-%s-001', $otherLaboratory->id, now()->format('my'))]);
    Tool::factory()->for($otherCategory)->for($laboratory)->create(['code' => sprintf('LABTM%d-MS-%s-009', $laboratory->id, now()->format('my'))]);

    $this
        ->actingAs($admin)
        ->get(route('admin.tools.generate-code', [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
        ]))
        ->assertOk()
        ->assertJson(['code' => $prefix.'006']);
});

test('generate code falls back to the category name initials when the code is empty', function () {
    $admin = User::factory()->kepalaLab()->create();
    $category = Category::factory()->create(['name' => 'Alat Ukur Presisi', 'code' => null]);
    $laboratory = Laboratory::factory()->create();

    $this
        ->actingAs($admin)
        ->get(route('admin.tools.generate-code', [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
        ]))
        ->assertOk()
        ->assertJson(['code' => sprintf('LABTM%d-AUP-%s-001', $laboratory->id, now()->format('my'))]);
});

test('generate code requires laboratory and category parameters', function () {
    $admin = User::factory()->kepalaLab()->create();

    $this
        ->actingAs($admin)
        ->get(route('admin.tools.generate-code'))
        ->assertSessionHasErrors(['laboratory_id', 'category_id']);
});

test('students are forbidden from generating tool codes', function () {
    $student = User::factory()->student()->create();
    $category = Category::factory()->create();
    $laboratory = Laboratory::factory()->create();

    $this
        ->actingAs($student)
        ->get(route('admin.tools.generate-code', [
            'laboratory_id' => $laboratory->id,
            'category_id' => $category->id,
        ]))
        ->assertForbidden();
});
