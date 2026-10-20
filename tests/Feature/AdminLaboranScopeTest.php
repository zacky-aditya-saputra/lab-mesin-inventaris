<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;

function createLaboranOfLaboratory(Laboratory $laboratory): User
{
    return User::factory()->create([
        'role' => 'laboran',
        'laboratory_id' => $laboratory->id,
    ]);
}

function createTicketForLaboratory(User $klien, Laboratory $laboratory, Tool $tool, string $status = 'PENDING'): LoanRequest
{
    $loan = LoanRequest::query()->create([
        'user_id' => $klien->id,
        'laboratory_id' => $laboratory->id,
        'ticket_number' => 'LOAN-'.strtoupper($laboratory->code).'-'.uniqid(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'purpose' => 'Pengujian scoping',
        'identity_card_path' => 'identity_cards/test.pdf',
        'loan_letter_path' => 'loan_letters/test.pdf',
        'status' => $status,
    ]);

    $loan->items()->create([
        'tool_id' => $tool->id,
        'requested_quantity' => 1,
    ]);

    return $loan;
}

test('laboran dashboard metrics are scoped to their managed laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);

    Tool::factory()->for(Category::factory())->for($labA)->count(3)->create();
    Tool::factory()->for(Category::factory())->for($labB)->count(2)->create();

    $klien = User::factory()->student()->create();
    $toolA = Tool::query()->where('laboratory_id', $labA->id)->firstOrFail();
    createTicketForLaboratory($klien, $labA, $toolA, 'PENDING');
    createTicketForLaboratory($klien, $labB, Tool::query()->where('laboratory_id', $labB->id)->firstOrFail(), 'PENDING');

    $response = $this->actingAs($laboran)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertViewHas('pendingLoansCount', 1)
        ->assertViewHas('totalTools', 3)
        ->assertViewHas('managedLaboratory', fn (Laboratory $laboratory) => $laboratory->is($labA))
        ->assertViewHas('laboratoryBreakdown', null);
});

test('kepala lab dashboard shows combined metrics and per laboratory breakdown', function () {
    $kepala = User::factory()->admin()->create();
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();

    Tool::factory()->for(Category::factory())->for($labA)->count(2)->create();
    Tool::factory()->for(Category::factory())->for($labB)->create();

    $klien = User::factory()->student()->create();
    createTicketForLaboratory($klien, $labA, Tool::query()->where('laboratory_id', $labA->id)->firstOrFail(), 'PENDING');
    createTicketForLaboratory($klien, $labB, Tool::query()->where('laboratory_id', $labB->id)->firstOrFail(), 'PENDING');

    $response = $this->actingAs($kepala)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertViewHas('pendingLoansCount', 2)
        ->assertViewHas('totalTools', 3)
        ->assertViewHas('managedLaboratory', null)
        ->assertViewHas('laboratoryBreakdown', fn ($breakdown) => $breakdown->count() === 2);
});

test('laboran loan ticket queue only lists tickets of their laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);
    $klien = User::factory()->student()->create();

    $ownTicket = createTicketForLaboratory($klien, $labA, Tool::factory()->for(Category::factory())->for($labA)->create(), 'PENDING');
    $foreignTicket = createTicketForLaboratory($klien, $labB, Tool::factory()->for(Category::factory())->for($labB)->create(), 'PENDING');

    $response = $this->actingAs($laboran)->get(route('admin.loans.index'));

    $response->assertOk()
        ->assertViewHas('loans', fn ($loans) => $loans->getCollection()->contains(fn (LoanRequest $loan) => $loan->is($ownTicket))
            && ! $loans->getCollection()->contains(fn (LoanRequest $loan) => $loan->is($foreignTicket)));
});

test('kepala lab can filter the loan ticket queue by laboratory', function () {
    $kepala = User::factory()->admin()->create();
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $klien = User::factory()->student()->create();

    $ticketA = createTicketForLaboratory($klien, $labA, Tool::factory()->for(Category::factory())->for($labA)->create(), 'PENDING');
    $ticketB = createTicketForLaboratory($klien, $labB, Tool::factory()->for(Category::factory())->for($labB)->create(), 'PENDING');

    $response = $this->actingAs($kepala)->get(route('admin.loans.index', ['laboratory_id' => $labB->id]));

    $response->assertOk()
        ->assertViewHas('loans', fn ($loans) => $loans->getCollection()->contains(fn (LoanRequest $loan) => $loan->is($ticketB))
            && ! $loans->getCollection()->contains(fn (LoanRequest $loan) => $loan->is($ticketA)));
});

test('laboran cannot review a ticket from another laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);
    $klien = User::factory()->student()->create();

    $foreignTicket = createTicketForLaboratory($klien, $labB, Tool::factory()->for(Category::factory())->for($labB)->create(), 'PENDING');

    $this
        ->actingAs($laboran)
        ->get(route('admin.loans.review', $foreignTicket))
        ->assertForbidden();
});

test('laboran cannot update the status of a ticket from another laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);
    $klien = User::factory()->student()->create();

    $foreignTicket = createTicketForLaboratory($klien, $labB, Tool::factory()->for(Category::factory())->for($labB)->create(), 'PENDING');

    $this
        ->actingAs($laboran)
        ->patch(route('admin.loans.update-status', $foreignTicket), [
            'action' => 'reject',
        ])
        ->assertForbidden();
});

test('laboran tool inventory only lists tools of their laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);

    $ownTool = Tool::factory()->for(Category::factory())->for($labA)->create();
    Tool::factory()->for(Category::factory())->for($labB)->create();

    $response = $this->actingAs($laboran)->get(route('admin.tools.index'));

    $response->assertOk()
        ->assertViewHas('tools', fn ($tools) => $tools->getCollection()->contains(fn (Tool $tool) => $tool->is($ownTool))
            && $tools->getCollection()->count() === 1);
});

test('laboran cannot edit a tool from another laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);

    $foreignTool = Tool::factory()->for(Category::factory())->for($labB)->create();

    $this
        ->actingAs($laboran)
        ->get(route('admin.tools.edit', $foreignTool))
        ->assertForbidden();
});

test('laboran cannot update a tool from another laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);

    $foreignTool = Tool::factory()->for(Category::factory())->for($labB)->create();

    $this
        ->actingAs($laboran)
        ->patch(route('admin.tools.update', $foreignTool), [
            'laboratory_id' => $labA->id,
            'category_id' => $foreignTool->category_id,
            'code' => $foreignTool->code,
            'name' => $foreignTool->name,
            'total_stock' => $foreignTool->total_stock,
            'is_active' => true,
        ])
        ->assertForbidden();
});

test('laboran tool creation is forced to their managed laboratory', function () {
    Storage::fake('public');

    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($labA);
    $category = Category::factory()->create();

    $this
        ->actingAs($laboran)
        ->post(route('admin.tools.store'), [
            'laboratory_id' => $labB->id,
            'category_id' => $category->id,
            'code' => 'LAB-FORCE-001',
            'name' => 'Alat Paksa Lab A',
            'total_stock' => 2,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.tools.index'));

    $tool = Tool::query()->where('code', 'LAB-FORCE-001')->firstOrFail();

    expect($tool->laboratory_id)->toBe($labA->id);
});

test('cancelled loans are excluded from default admin loan queue but visible in reports and status filter', function () {
    $lab = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($lab);
    $klien = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->for($lab)->create();

    $pending = createTicketForLaboratory($klien, $lab, $tool, 'PENDING');
    $cancelled = createTicketForLaboratory($klien, $lab, $tool, 'CANCELLED');

    // Default queue excludes CANCELLED
    $responseDefault = $this->actingAs($laboran)->get(route('admin.loans.index'));
    $responseDefault->assertOk();
    $loans = $responseDefault->viewData('loans');
    expect($loans->pluck('id'))->toContain($pending->id)
        ->and($loans->pluck('id'))->not->toContain($cancelled->id);

    // Filtering status=CANCELLED displays cancelled tickets
    $responseCancelled = $this->actingAs($laboran)->get(route('admin.loans.index', ['status' => 'CANCELLED']));
    $responseCancelled->assertOk();
    $cancelledLoans = $responseCancelled->viewData('loans');
    expect($cancelledLoans->pluck('id'))->toContain($cancelled->id)
        ->and($cancelledLoans->pluck('id'))->not->toContain($pending->id);

    // Reports view displays all tickets including CANCELLED
    $responseReports = $this->actingAs($laboran)->get(route('admin.loans.index', ['view' => 'reports']));
    $responseReports->assertOk();
    $reportsLoans = $responseReports->viewData('loans');
    expect($reportsLoans->pluck('id'))->toContain($pending->id)
        ->and($reportsLoans->pluck('id'))->toContain($cancelled->id);
});

test('admin can filter loans by created_at date range', function () {
    $this->freezeTime();

    $lab = Laboratory::factory()->create();
    $laboran = createLaboranOfLaboratory($lab);
    $klien = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->for($lab)->create();

    $loanToday = createTicketForLaboratory($klien, $lab, $tool, 'PENDING');
    $loanOld = createTicketForLaboratory($klien, $lab, $tool, 'PENDING');
    $loanOld->created_at = now()->subDays(10);
    $loanOld->saveQuietly();

    $response = $this->actingAs($laboran)->get(route('admin.loans.index', [
        'from_date' => now()->subDays(2)->toDateString(),
        'to_date' => now()->toDateString(),
    ]));

    $response->assertOk();
    $loans = $response->viewData('loans');
    expect($loans->pluck('id'))->toContain($loanToday->id)
        ->and($loans->pluck('id'))->not->toContain($loanOld->id);
});
