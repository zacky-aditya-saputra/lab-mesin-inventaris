<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\Tool;
use App\Models\User;

test('laboran cannot delete a tool belonging to another laboratory', function () {
    $labA = Laboratory::factory()->create(['name' => 'Lab A']);
    $labB = Laboratory::factory()->create(['name' => 'Lab B']);
    $laboranA = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);

    $foreignTool = Tool::factory()->for(Category::factory())->for($labB)->create();

    $this
        ->actingAs($laboranA)
        ->from(route('admin.tools.index'))
        ->delete(route('admin.tools.destroy', $foreignTool))
        ->assertForbidden();

    expect($foreignTool->fresh())->not->toBeNull()
        ->and($foreignTool->fresh()->deleted_at)->toBeNull();
});

test('laboran can delete a tool belonging to their own laboratory', function () {
    $labA = Laboratory::factory()->create(['name' => 'Lab A']);
    $laboranA = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);

    $ownTool = Tool::factory()->for(Category::factory())->for($labA)->create();

    $this
        ->actingAs($laboranA)
        ->delete(route('admin.tools.destroy', $ownTool))
        ->assertRedirect(route('admin.tools.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($ownTool);
});

test('kepala lab can delete any tool regardless of laboratory', function () {
    $labB = Laboratory::factory()->create(['name' => 'Lab B']);
    $kepalaLab = User::factory()->kepalaLab()->create();

    $foreignTool = Tool::factory()->for(Category::factory())->for($labB)->create();

    $this
        ->actingAs($kepalaLab)
        ->delete(route('admin.tools.destroy', $foreignTool))
        ->assertRedirect(route('admin.tools.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($foreignTool);
});

test('super admin can delete any tool regardless of laboratory', function () {
    $labB = Laboratory::factory()->create(['name' => 'Lab B']);
    $superAdmin = User::factory()->create(['role' => 'admin']);

    $foreignTool = Tool::factory()->for(Category::factory())->for($labB)->create();

    $this
        ->actingAs($superAdmin)
        ->delete(route('admin.tools.destroy', $foreignTool))
        ->assertRedirect(route('admin.tools.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($foreignTool);
});

test('students remain forbidden from deleting tools', function () {
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->for(Laboratory::factory())->create();

    $this
        ->actingAs($student)
        ->delete(route('admin.tools.destroy', $tool))
        ->assertForbidden();

    expect($tool->fresh())->not->toBeNull();
});
