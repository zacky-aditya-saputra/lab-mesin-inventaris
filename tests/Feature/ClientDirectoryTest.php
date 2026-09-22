<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;

function createClientBorrower(array $attributes = []): User
{
    return User::factory()->klien()->create(array_merge([
        'phone_number' => '081234567890',
        'institution' => 'Teknik Mesin S1',
    ], $attributes));
}

function createLoanTicketFor(User $client, Laboratory $laboratory): void
{
    $tool = Tool::factory()->for(Category::factory())->for($laboratory)->create();

    LoanRequest::query()->create([
        'user_id' => $client->id,
        'laboratory_id' => $laboratory->id,
        'ticket_number' => 'LOAN-'.strtoupper($laboratory->code).'-'.uniqid(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'purpose' => 'Pengujian direktori klien',
        'identity_card_path' => 'identity_cards/test.pdf',
        'loan_letter_path' => 'loan_letters/test.pdf',
        'status' => 'PENDING',
    ])->items()->create([
        'tool_id' => $tool->id,
        'requested_quantity' => 1,
    ]);
}

test('laboran client directory only lists clients who borrowed in their laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);

    $clientA = createClientBorrower(['name' => 'Klien Lab A', 'identity_card_path' => 'identity_cards/a.pdf']);
    $clientB = createClientBorrower(['name' => 'Klien Lab B', 'identity_card_path' => 'identity_cards/b.pdf']);

    createLoanTicketFor($clientA, $labA);
    createLoanTicketFor($clientB, $labB);

    $response = $this
        ->actingAs($laboran)
        ->get(route('admin.clients.index'))
        ->assertOk()
        ->assertViewHas('clients', fn ($clients) => $clients->getCollection()->contains(fn (User $client) => $client->is($clientA))
            && ! $clients->getCollection()->contains(fn (User $client) => $client->is($clientB)));

    $response->assertSee('Klien Lab A')->assertSee('wa.me/6281234567890');
    $response->assertDontSee('Klien Lab B');
});

test('laboran client loan counts are scoped to their laboratory', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);
    $client = createClientBorrower();

    createLoanTicketFor($client, $labA);
    createLoanTicketFor($client, $labB);

    $this
        ->actingAs($laboran)
        ->get(route('admin.clients.index'))
        ->assertOk()
        ->assertViewHas('clients', fn ($clients) => $clients->first()->loan_requests_count === 1);
});

test('kepala lab sees all clients and can filter the directory by laboratory', function () {
    $kepala = User::factory()->kepalaLab()->create();
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();

    $clientA = createClientBorrower(['name' => 'Klien Lab A']);
    $clientB = createClientBorrower(['name' => 'Klien Lab B']);

    createLoanTicketFor($clientA, $labA);
    createLoanTicketFor($clientB, $labB);

    $this
        ->actingAs($kepala)
        ->get(route('admin.clients.index'))
        ->assertOk()
        ->assertViewHas('clients', fn ($clients) => $clients->total() === 2)
        ->assertSee('Klien Lab A')
        ->assertSee('Klien Lab B');

    $this
        ->actingAs($kepala)
        ->get(route('admin.clients.index', ['laboratory_id' => $labB->id]))
        ->assertOk()
        ->assertViewHas('clients', fn ($clients) => $clients->getCollection()->contains(fn (User $client) => $client->is($clientB))
            && ! $clients->getCollection()->contains(fn (User $client) => $client->is($clientA)));
});

test('guests and clients cannot access the client directory', function () {
    $this->get(route('admin.clients.index'))->assertRedirect(route('login'));

    $klien = User::factory()->klien()->create();

    $this->actingAs($klien)->get(route('admin.clients.index'))->assertForbidden();
});
