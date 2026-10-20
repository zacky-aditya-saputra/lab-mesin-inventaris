<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use App\Models\User;

test('only kepala_lab can access laboratory management', function () {
    $kepalaLab = User::factory()->kepalaLab()->create();
    $laboran = User::factory()->laboran()->create();
    $student = User::factory()->student()->create();

    $this->actingAs($kepalaLab)->get(route('admin.laboratories.index'))->assertOk();
    $this->actingAs($laboran)->get(route('admin.laboratories.index'))->assertForbidden();
    $this->actingAs($student)->get(route('admin.laboratories.index'))->assertForbidden();
});

test('kepala_lab can create a new laboratory with uppercase code and auto-generated slug', function () {
    $kepalaLab = User::factory()->kepalaLab()->create();

    $response = $this->actingAs($kepalaLab)->post(route('admin.laboratories.store'), [
        'name' => 'Laboratorium Mekatronika Cerdas',
        'code' => 'lmc',
        'location' => 'Gedung D, Lantai 3',
        'description' => 'Fasilitas robotika dan otomasi',
        'is_active' => '1',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('laboratories', [
        'name' => 'Laboratorium Mekatronika Cerdas',
        'code' => 'LMC',
        'slug' => 'laboratorium-mekatronika-cerdas',
        'location' => 'Gedung D, Lantai 3',
        'is_active' => true,
    ]);
});

test('kepala_lab can update an existing laboratory', function () {
    $kepalaLab = User::factory()->kepalaLab()->create();
    $lab = Laboratory::factory()->create([
        'name' => 'Lab Lama',
        'code' => 'LLM',
    ]);

    $response = $this->actingAs($kepalaLab)->put(route('admin.laboratories.update', $lab), [
        'name' => 'Lab Diperbarui',
        'code' => 'LDP',
        'location' => 'Gedung A',
        'description' => 'Keterangan baru',
        'is_active' => '1',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('laboratories', [
        'id' => $lab->id,
        'name' => 'Lab Diperbarui',
        'code' => 'LDP',
        'location' => 'Gedung A',
    ]);
});

test('kepala_lab can toggle laboratory active status', function () {
    $kepalaLab = User::factory()->kepalaLab()->create();
    $lab = Laboratory::factory()->create(['is_active' => true]);

    $this->actingAs($kepalaLab)
        ->patch(route('admin.laboratories.toggle-active', $lab))
        ->assertSessionHas('success');

    expect($lab->refresh()->is_active)->toBeFalse();

    $this->actingAs($kepalaLab)
        ->patch(route('admin.laboratories.toggle-active', $lab))
        ->assertSessionHas('success');

    expect($lab->refresh()->is_active)->toBeTrue();
});

test('laboratory deletion is blocked when it has related tools', function () {
    $kepalaLab = User::factory()->kepalaLab()->create();
    $lab = Laboratory::factory()->create();
    Tool::factory()->for(Category::factory())->for($lab)->create();

    $response = $this->actingAs($kepalaLab)->delete(route('admin.laboratories.destroy', $lab));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('laboratories', ['id' => $lab->id]);
});

test('unused laboratory can be successfully deleted', function () {
    $kepalaLab = User::factory()->kepalaLab()->create();
    $lab = Laboratory::factory()->create();

    $response = $this->actingAs($kepalaLab)->delete(route('admin.laboratories.destroy', $lab));

    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('laboratories', ['id' => $lab->id]);
});
