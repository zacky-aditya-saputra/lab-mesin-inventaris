<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use App\Models\User;

test('catalog page is accessible by guests', function () {
    $this->get(route('catalog.index'))->assertOk();
});

test('catalog page does not display inactive tools', function () {
    $category = Category::factory()->create();
    Tool::factory()->for($category)->create(['name' => 'Jangka Sorong Digital Mitutoyo']);
    Tool::factory()->inactive()->for($category)->create(['name' => 'Alat Rusak Rahasia']);

    $this->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Jangka Sorong Digital Mitutoyo')
        ->assertDontSee('Alat Rusak Rahasia');
});

test('catalog page renders category relation and availability status without errors', function () {
    $alatUkur = Category::factory()->create(['name' => 'Alat Ukur Presisi']);
    $mesinPerkakas = Category::factory()->create(['name' => 'Mesin Perkakas']);
    Tool::factory()->for($alatUkur)->create(['name' => 'Mikrometer Luar', 'available_stock' => 6]);
    Tool::factory()->for($mesinPerkakas)->create(['name' => 'Mesin Bubut Colchester', 'available_stock' => 2]);

    $this->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Alat Ukur Presisi')
        ->assertSee('Mesin Perkakas')
        ->assertSee('Mikrometer Luar')
        ->assertSee('Mesin Bubut Colchester')
        ->assertSee('Tersedia')
        ->assertSee('Stok Terbatas');
});

test('catalog detail page displays an active tool', function () {
    $category = Category::factory()->create();
    $tool = Tool::factory()->for($category)->create(['name' => 'Jangka Sorong Digital']);

    $this->get(route('catalog.show', $tool->slug))
        ->assertOk()
        ->assertSee('Jangka Sorong Digital')
        ->assertSee($tool->code)
        ->assertSee($category->name);
});

test('catalog detail page returns 404 for inactive tools', function () {
    $category = Category::factory()->create();
    $tool = Tool::factory()->inactive()->for($category)->create();

    $this->get(route('catalog.show', $tool->slug))->assertNotFound();
});

test('catalog detail page returns 404 when the tool slug does not exist', function () {
    $this->get(route('catalog.show', 'slug-tidak-ada'))->assertNotFound();
});

test('student sees loan history and request loan links in catalog navbar', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Riwayat Pengajuan')
        ->assertSee(route('loans.history', [], false))
        ->assertSee(route('loans.create', [], false))
        ->assertDontSee('Kelola Lab (Dashboard)');
});

test('admin sees dashboard links instead of student loan links in catalog navbar', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Kelola Lab (Dashboard)')
        ->assertSee(route('admin.dashboard', [], false))
        ->assertDontSee('Riwayat Pengajuan')
        ->assertDontSee(route('loans.history', [], false));
});

test('catalog page provides all active laboratories and tools with lab metadata', function () {
    $category = Category::factory()->create(['name' => 'Elektronika']);
    $lab1 = Laboratory::factory()->create(['name' => 'Laboratorium Perancangan', 'code' => 'LPR', 'slug' => 'laboratorium-perancangan']);
    $lab2 = Laboratory::factory()->create(['name' => 'Laboratorium Energi', 'code' => 'LEN', 'slug' => 'laboratorium-energi']);

    $tool1 = Tool::factory()->for($category)->for($lab1)->create(['name' => 'Oscilloscope Digital']);
    $tool2 = Tool::factory()->for($category)->for($lab2)->create(['name' => 'Bomb Calorimeter']);

    $response = $this->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Laboratorium Perancangan')
        ->assertSee('Laboratorium Energi')
        ->assertSee('Oscilloscope Digital')
        ->assertSee('Bomb Calorimeter')
        ->assertViewHas('laboratories')
        ->assertViewHas('toolsList')
        ->assertViewHas('selectedLabSlug', 'all');

    $toolsList = $response->viewData('toolsList');
    expect(collect($toolsList)->pluck('laboratory_slug'))->toContain('laboratorium-perancangan', 'laboratorium-energi');
});

test('catalog page accepts lab parameter to preselect laboratory filter', function () {
    $lab = Laboratory::factory()->create(['name' => 'Laboratorium Material', 'code' => 'LMAT', 'slug' => 'laboratorium-material']);

    $this->get(route('catalog.index', ['lab' => 'laboratorium-material']))
        ->assertOk()
        ->assertViewHas('selectedLabSlug', 'laboratorium-material')
        ->assertSee('laboratorium-material');
});
