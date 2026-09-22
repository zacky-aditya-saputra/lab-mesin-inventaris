<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createKlienWithCompleteProfile(array $attributes = []): User
{
    $identityCardPath = UploadedFile::fake()->create('ktm.pdf', 200, 'application/pdf')->store('identity_cards', 'public');

    return User::factory()->student()->create(array_merge([
        'identity_number' => '32602100001',
        'phone_number' => '081234567890',
        'institution' => 'Teknik Mesin S1',
        'identity_card_path' => $identityCardPath,
    ], $attributes));
}

function loanLetterFile(): UploadedFile
{
    return UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf');
}

test('student can open the loan request form and see the list of available tools', function () {
    $student = createKlienWithCompleteProfile();
    $category = Category::factory()->create();
    $lab = Laboratory::factory()->create();
    $tool = Tool::factory()->for($category)->for($lab)->create(['available_stock' => 5]);
    Tool::factory()->for($category)->for($lab)->inactive()->create();

    $response = $this
        ->actingAs($student)
        ->get(route('loans.create'))
        ->assertOk()
        ->assertViewHas('toolsList')
        ->assertViewHas('laboratories')
        ->assertViewHas('isProfileComplete', true);

    $toolsList = $response->viewData('toolsList');
    $laboratories = $response->viewData('laboratories');

    expect($toolsList)->toHaveCount(1)
        ->and($toolsList[0]['id'])->toBe($tool->id)
        ->and($toolsList[0]['code'])->toBe($tool->code)
        ->and($toolsList[0]['name'])->toBe($tool->name)
        ->and($toolsList[0]['laboratory_id'])->toBe($lab->id)
        ->and($toolsList[0]['available_stock'])->toBe(5)
        ->and($toolsList[0]['image'])->toBeNull()
        ->and($laboratories)->toHaveCount(1);
});

test('student can submit a multi-item loan request stored with pending status and a unique ticket number', function () {
    $this->freezeTime();
    Storage::fake('public');

    $student = createKlienWithCompleteProfile();
    $category = Category::factory()->create();
    $lab = Laboratory::factory()->create();
    $firstTool = Tool::factory()->for($category)->for($lab)->create(['available_stock' => 5]);
    $secondTool = Tool::factory()->for($category)->for($lab)->create(['available_stock' => 5]);

    $payload = fn (): array => [
        'laboratory_id' => $lab->id,
        'loan_letter' => loanLetterFile(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'purpose' => 'Praktikum pengukuran presisi',
        'items' => [
            ['tool_id' => $firstTool->id, 'quantity' => 1],
            ['tool_id' => $secondTool->id, 'quantity' => 2],
        ],
    ];

    $this
        ->actingAs($student)
        ->post(route('loans.store'), $payload())
        ->assertRedirect(route('loans.history'))
        ->assertSessionHas('success');

    $firstTicket = LoanRequest::query()->where('user_id', $student->id)->orderBy('id')->sole()->ticket_number;

    $this
        ->actingAs($student)
        ->post(route('loans.store'), $payload())
        ->assertRedirect(route('loans.history'));

    $loanRequest = LoanRequest::query()->where('user_id', $student->id)->orderByDesc('id')->firstOrFail();

    expect($loanRequest->ticket_number)->not->toBe($firstTicket)
        ->and($loanRequest->ticket_number)->toMatch('/^LOAN-'.now()->format('Ymd').'-[A-Z0-9]{4}$/')
        ->and($loanRequest->status)->toBe('PENDING')
        ->and($loanRequest->laboratory_id)->toBe($lab->id)
        ->and($loanRequest->start_date->toDateString())->toBe(now()->toDateString())
        ->and($loanRequest->end_date->toDateString())->toBe(now()->addDays(7)->toDateString())
        ->and($loanRequest->purpose)->toBe('Praktikum pengukuran presisi')
        ->and($loanRequest->identity_card_path)->toBe($student->identity_card_path)
        ->and($loanRequest->loan_letter_path)->toStartWith('loan_letters/')
        ->and($student->refresh()->identity_number)->toBe('32602100001')
        ->and($student->refresh()->phone_number)->toBe('081234567890');

    $items = $loanRequest->items->keyBy('tool_id');

    expect($items)->toHaveCount(2)
        ->and($items[$firstTool->id]->requested_quantity)->toBe(1)
        ->and($items[$firstTool->id]->approved_quantity)->toBeNull()
        ->and($items[$firstTool->id]->return_condition)->toBeNull()
        ->and($items[$secondTool->id]->requested_quantity)->toBe(2)
        ->and($items[$secondTool->id]->approved_quantity)->toBeNull();

    Storage::disk('public')->assertExists($loanRequest->identity_card_path);
    Storage::disk('public')->assertExists($loanRequest->loan_letter_path);
});

test('student loan submission is rejected when the return date precedes the start date', function () {
    $this->freezeTime();

    $student = createKlienWithCompleteProfile();
    $lab = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($lab)->create(['available_stock' => 5]);

    $this
        ->actingAs($student)
        ->post(route('loans.store'), [
            'laboratory_id' => $lab->id,
            'loan_letter' => loanLetterFile(),
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'purpose' => 'Praktikum pengukuran presisi',
            'items' => [
                ['tool_id' => $tool->id, 'quantity' => 1],
            ],
        ])
        ->assertSessionHasErrors('end_date');

    $this->assertDatabaseCount('loan_requests', 0);
});

test('student loan submission is rejected when the quantity exceeds the available stock', function () {
    $this->freezeTime();

    $student = createKlienWithCompleteProfile();
    $lab = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($lab)->create(['total_stock' => 2, 'available_stock' => 2]);

    $this
        ->actingAs($student)
        ->post(route('loans.store'), [
            'laboratory_id' => $lab->id,
            'loan_letter' => loanLetterFile(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'purpose' => 'Praktikum pengukuran presisi',
            'items' => [
                ['tool_id' => $tool->id, 'quantity' => 5],
            ],
        ])
        ->assertSessionHasErrors([
            'items.0.quantity' => "Jumlah pinjam untuk alat {$tool->name} melebihi stok tersedia (2 unit).",
        ]);

    $this->assertDatabaseCount('loan_requests', 0);
});

test('student loan submission is rejected when a selected tool belongs to another laboratory', function () {
    $this->freezeTime();

    $student = createKlienWithCompleteProfile();
    $targetLab = Laboratory::factory()->create();
    $otherLab = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($otherLab)->create(['available_stock' => 5]);

    $this
        ->actingAs($student)
        ->post(route('loans.store'), [
            'laboratory_id' => $targetLab->id,
            'loan_letter' => loanLetterFile(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'purpose' => 'Praktikum pengukuran presisi',
            'items' => [
                ['tool_id' => $tool->id, 'quantity' => 1],
            ],
        ])
        ->assertSessionHasErrors([
            'items.0.tool_id' => "Alat {$tool->name} bukan milik laboratorium tujuan yang dipilih.",
        ]);

    $this->assertDatabaseCount('loan_requests', 0);
});

test('student loan submission is rejected when the profile has no identity card document', function () {
    $this->freezeTime();
    Storage::fake('public');

    $student = createKlienWithCompleteProfile(['identity_card_path' => null]);
    $lab = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($lab)->create(['available_stock' => 5]);

    $this
        ->actingAs($student)
        ->post(route('loans.store'), [
            'laboratory_id' => $lab->id,
            'loan_letter' => loanLetterFile(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'purpose' => 'Praktikum pengukuran presisi',
            'items' => [
                ['tool_id' => $tool->id, 'quantity' => 1],
            ],
        ])
        ->assertSessionHas('error');

    $this->assertDatabaseCount('loan_requests', 0);
});

test('general public client can submit a loan request using the identity document stored on the profile', function () {
    $this->freezeTime();
    Storage::fake('public');

    $client = createKlienWithCompleteProfile([
        'identity_type' => 'umum',
        'identity_number' => '3301123456789012',
        'phone_number' => '089876543210',
        'institution' => 'Kota Semarang',
    ]);
    $lab = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($lab)->create(['available_stock' => 3]);

    $this
        ->actingAs($client)
        ->post(route('loans.store'), [
            'laboratory_id' => $lab->id,
            'loan_letter' => loanLetterFile(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Pengujian material proyek pribadi',
            'items' => [
                ['tool_id' => $tool->id, 'quantity' => 1],
            ],
        ])
        ->assertRedirect(route('loans.history'))
        ->assertSessionHas('success');

    $loanRequest = LoanRequest::query()->where('user_id', $client->id)->sole();

    expect($loanRequest->status)->toBe('PENDING')
        ->and($loanRequest->laboratory_id)->toBe($lab->id)
        ->and($loanRequest->purpose)->toBe('Pengujian material proyek pribadi')
        ->and($loanRequest->identity_card_path)->toBe($client->identity_card_path)
        ->and($loanRequest->loan_letter_path)->toStartWith('loan_letters/');

    Storage::disk('public')->assertExists($loanRequest->loan_letter_path);
});

test('guests are redirected to login when accessing the student loan routes', function () {
    $this->get(route('loans.create'))->assertRedirect(route('login'));
    $this->post(route('loans.store'))->assertRedirect(route('login'));
    $this->get(route('loans.history'))->assertRedirect(route('login'));
});

test('admins are forbidden from accessing the student loan routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('loans.create'))->assertForbidden();
    $this->actingAs($admin)->post(route('loans.store'))->assertForbidden();
    $this->actingAs($admin)->get(route('loans.history'))->assertForbidden();
});
