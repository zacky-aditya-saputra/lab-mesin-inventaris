<?php

use App\Models\Laboratory;
use App\Models\StudentActivityLog;
use App\Models\User;

test('unauthenticated user is redirected to login for activity logs', function () {
    $this->get(route('activity-logs.index'))->assertRedirect(route('login'));
    $this->get(route('activity-logs.create'))->assertRedirect(route('login'));
    $this->get(route('admin.activity-logs.index'))->assertRedirect(route('login'));
});

test('student can view activity logs list and see only their own logs', function () {
    $student = User::factory()->create(['role' => 'klien']);
    $otherStudent = User::factory()->create(['role' => 'klien']);
    $lab = Laboratory::factory()->create();

    $myLog = StudentActivityLog::factory()->create([
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
        'title' => 'Riset Pembubutan Poros',
    ]);

    $otherLog = StudentActivityLog::factory()->create([
        'user_id' => $otherStudent->id,
        'laboratory_id' => $lab->id,
        'title' => 'Praktikum CNC Milling',
    ]);

    $response = $this->actingAs($student)->get(route('activity-logs.index'));

    $response->assertOk();
    $response->assertSee('Riset Pembubutan Poros');
    $response->assertDontSee('Praktikum CNC Milling');
});

test('student can view create form with active laboratories', function () {
    $student = User::factory()->create(['role' => 'klien']);
    $activeLab = Laboratory::factory()->create(['name' => 'Lab Fabrikasi Aktif', 'is_active' => true]);
    $inactiveLab = Laboratory::factory()->create(['name' => 'Lab Nonaktif', 'is_active' => false]);

    $response = $this->actingAs($student)->get(route('activity-logs.create'));

    $response->assertOk();
    $response->assertSee('Lab Fabrikasi Aktif');
    $response->assertDontSee('Lab Nonaktif');
});

test('student can create a valid activity log', function () {
    $student = User::factory()->create(['role' => 'klien']);
    $lab = Laboratory::factory()->create();

    $data = [
        'laboratory_id' => $lab->id,
        'activity_date' => now()->toDateString(),
        'time_in' => '09:00',
        'time_out' => '12:00',
        'activity_type' => 'Penelitian',
        'title' => 'Pengujian Getaran Mesin Bubut',
        'description' => 'Melakukan pengujian getaran pada kecepatan spindle 1200 RPM.',
    ];

    $response = $this->actingAs($student)->post(route('activity-logs.store'), $data);

    $response->assertRedirect(route('activity-logs.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_activity_logs', [
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
        'title' => 'Pengujian Getaran Mesin Bubut',
        'status' => 'PENDING',
    ]);
});

test('student cannot create activity log with future date or invalid time_out', function () {
    $student = User::factory()->create(['role' => 'klien']);
    $lab = Laboratory::factory()->create();

    // Future date
    $response = $this->actingAs($student)->post(route('activity-logs.store'), [
        'laboratory_id' => $lab->id,
        'activity_date' => now()->addDay()->toDateString(),
        'time_in' => '09:00',
        'time_out' => '12:00',
        'activity_type' => 'Praktikum',
        'title' => 'Kegiatan Masa Depan',
    ]);
    $response->assertSessionHasErrors(['activity_date']);

    // time_out before time_in
    $response2 = $this->actingAs($student)->post(route('activity-logs.store'), [
        'laboratory_id' => $lab->id,
        'activity_date' => now()->toDateString(),
        'time_in' => '14:00',
        'time_out' => '10:00',
        'activity_type' => 'Praktikum',
        'title' => 'Jam Tidak Valid',
    ]);
    $response2->assertSessionHasErrors(['time_out']);
});

test('student can delete pending activity log but not verified log or log belonging to others', function () {
    $student = User::factory()->create(['role' => 'klien']);
    $otherStudent = User::factory()->create(['role' => 'klien']);
    $lab = Laboratory::factory()->create();

    $pendingLog = StudentActivityLog::factory()->create([
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
        'status' => 'PENDING',
    ]);

    $verifiedLog = StudentActivityLog::factory()->verified()->create([
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
    ]);

    $otherLog = StudentActivityLog::factory()->create([
        'user_id' => $otherStudent->id,
        'laboratory_id' => $lab->id,
        'status' => 'PENDING',
    ]);

    // 1. Can delete own pending log
    $this->actingAs($student)
        ->delete(route('activity-logs.destroy', $pendingLog))
        ->assertRedirect(route('activity-logs.index'))
        ->assertSessionHas('success');
    $this->assertDatabaseMissing('student_activity_logs', ['id' => $pendingLog->id]);

    // 2. Cannot delete verified log
    $this->actingAs($student)
        ->delete(route('activity-logs.destroy', $verifiedLog))
        ->assertSessionHas('error');
    $this->assertDatabaseHas('student_activity_logs', ['id' => $verifiedLog->id]);

    // 3. Cannot delete other student's log (403)
    $this->actingAs($student)
        ->delete(route('activity-logs.destroy', $otherLog))
        ->assertForbidden();
    $this->assertDatabaseHas('student_activity_logs', ['id' => $otherLog->id]);
});

test('laboran can only see logs of their laboratory', function () {
    $labA = Laboratory::factory()->create(['name' => 'Lab Presisi']);
    $labB = Laboratory::factory()->create(['name' => 'Lab Material']);

    $laboranA = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);

    $logA = StudentActivityLog::factory()->create([
        'laboratory_id' => $labA->id,
        'title' => 'Pengukuran Mikrometer Presisi',
    ]);

    $logB = StudentActivityLog::factory()->create([
        'laboratory_id' => $labB->id,
        'title' => 'Uji Tarik Logam Baja',
    ]);

    $response = $this->actingAs($laboranA)->get(route('admin.activity-logs.index'));

    $response->assertOk();
    $response->assertSee('Pengukuran Mikrometer Presisi');
    $response->assertDontSee('Uji Tarik Logam Baja');
});

test('laboran can verify and reject activity logs in their laboratory but gets 403 for other lab', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();

    $laboranA = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);

    $logA = StudentActivityLog::factory()->create([
        'laboratory_id' => $labA->id,
        'status' => 'PENDING',
    ]);

    $logB = StudentActivityLog::factory()->create([
        'laboratory_id' => $labB->id,
        'status' => 'PENDING',
    ]);

    // Laboran A verifies logA -> success
    $this->actingAs($laboranA)
        ->patch(route('admin.activity-logs.verify', $logA))
        ->assertSessionHas('success');
    expect($logA->fresh()->status)->toBe('VERIFIED')
        ->and($logA->fresh()->verified_by)->toBe($laboranA->id);

    // Laboran A tries to verify logB -> 403
    $this->actingAs($laboranA)
        ->patch(route('admin.activity-logs.verify', $logB))
        ->assertForbidden();

    // Laboran A rejects logA
    $this->actingAs($laboranA)
        ->patch(route('admin.activity-logs.reject', $logA))
        ->assertSessionHas('success');
    expect($logA->fresh()->status)->toBe('REJECTED');
});

test('kepala lab can view all logs across laboratories and can verify or reject them', function () {
    $labA = Laboratory::factory()->create();
    $labB = Laboratory::factory()->create();

    $kepalaLab = User::factory()->kepalaLab()->create();

    $logA = StudentActivityLog::factory()->create([
        'laboratory_id' => $labA->id,
        'title' => 'Log Lab A',
        'status' => 'PENDING',
    ]);

    $logB = StudentActivityLog::factory()->create([
        'laboratory_id' => $labB->id,
        'title' => 'Log Lab B',
        'status' => 'PENDING',
    ]);

    $response = $this->actingAs($kepalaLab)->get(route('admin.activity-logs.index'));
    $response->assertOk();
    $response->assertSee('Log Lab A');
    $response->assertSee('Log Lab B');

    // Kepala lab can verify log in Lab A
    $this->actingAs($kepalaLab)
        ->patch(route('admin.activity-logs.verify', $logA))
        ->assertSessionHas('success');
    expect($logA->fresh()->status)->toBe('VERIFIED');

    // Kepala lab can verify log in Lab B
    $this->actingAs($kepalaLab)
        ->patch(route('admin.activity-logs.verify', $logB))
        ->assertSessionHas('success');
    expect($logB->fresh()->status)->toBe('VERIFIED');
});

test('admin can filter activity logs by activity_date range', function () {
    $this->freezeTime();

    $lab = Laboratory::factory()->create();
    $laboran = User::factory()->laboran()->create(['laboratory_id' => $lab->id]);

    $logRecent = StudentActivityLog::factory()->create([
        'laboratory_id' => $lab->id,
        'activity_date' => now()->toDateString(),
        'title' => 'Kegiatan Hari Ini',
    ]);

    $logOld = StudentActivityLog::factory()->create([
        'laboratory_id' => $lab->id,
        'activity_date' => now()->subDays(15)->toDateString(),
        'title' => 'Kegiatan Masa Lalu',
    ]);

    $response = $this->actingAs($laboran)->get(route('admin.activity-logs.index', [
        'from_date' => now()->subDays(3)->toDateString(),
        'to_date' => now()->toDateString(),
    ]));

    $response->assertOk();
    $logs = $response->viewData('logs');
    expect($logs->pluck('id'))->toContain($logRecent->id)
        ->and($logs->pluck('id'))->not->toContain($logOld->id);
});
