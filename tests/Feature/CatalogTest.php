<?php

use App\Models\Category;
use App\Models\Tool;

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
