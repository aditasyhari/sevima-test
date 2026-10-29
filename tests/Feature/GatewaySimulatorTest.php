<?php

use App\Models\Institusi;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('simulator page can be rendered', function () {
    $univA = Institusi::factory()->create(['kode' => 'UNIV-A']);
    $student = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    Tagihan::factory()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
    ]);

    $response = $this->get('/simulator');
    $response->assertStatus(200);
});

test('simulator trigger dispatches mock webhook to endpoint successfully', function () {
    Http::fake([
        '*' => Http::response(['status' => 'success', 'message' => 'Processed'], 200),
    ]);

    $univA = Institusi::factory()->create([
        'kode' => 'UNIV-A',
        'webhook_secret' => 'test_secret_123',
    ]);
    $student = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    $tagihan = Tagihan::factory()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'nomor_tagihan' => 'TAG-SIM-001',
        'nominal' => 3000000,
    ]);

    $response = $this->postJson('/simulator/trigger', [
        'institusi_id' => $univA->id,
        'tagihan_id' => $tagihan->id,
        'event' => 'payment.success',
        'tamper_signature' => false,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 'success');
    $response->assertJsonPath('dispatch_result.status_code', 200);
    $response->assertJsonPath('tampered', false);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-Signature')
            && $request['institution_code'] === 'UNIV-A'
            && $request['bill_number'] === 'TAG-SIM-001';
    });
});
