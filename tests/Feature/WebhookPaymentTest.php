<?php

use App\Models\Institusi;
use App\Models\RiwayatTransaksi;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\WebhookAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('valid webhook with correct HMAC-SHA256 signature marks bill as lunas and records ledger', function () {
    $secret = 'super_secret_key_univ_a';
    $univA = Institusi::factory()->create([
        'kode' => 'UNIV-A',
        'webhook_secret' => $secret,
    ]);

    $student = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    $tagihan = Tagihan::factory()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'nomor_tagihan' => 'TAG-TEST-001',
        'nominal' => 4500000.00,
        'status' => Tagihan::STATUS_BELUM_BAYAR,
    ]);

    $payload = [
        'event' => 'payment.success',
        'institution_code' => 'UNIV-A',
        'bill_number' => 'TAG-TEST-001',
        'amount' => 4500000.00,
        'transaction_reference' => 'TRX-REF-12345',
        'payment_time' => now()->toIso8601String(),
    ];

    $rawPayload = json_encode($payload);
    $signature = hash_hmac('sha256', $rawPayload, $secret);

    $response = $this->withServerVariables([
        'HTTP_X_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ])->call('POST', '/api/v1/webhook/payment', [], [], [], [], $rawPayload);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 'success');

    // Verify database state
    $tagihan->refresh();
    expect($tagihan->status)->toBe(Tagihan::STATUS_LUNAS);

    // Verify transaction history
    $history = RiwayatTransaksi::where('tagihan_id', $tagihan->id)->first();
    expect($history)->not->toBeNull()
        ->and($history->referensi_gateway)->toBe('TRX-REF-12345')
        ->and($history->event_type)->toBe('payment.success');

    // Verify audit log
    $audit = WebhookAuditLog::where('signature_header', $signature)->first();
    expect($audit)->not->toBeNull()
        ->and($audit->is_signature_valid)->toBeTrue()
        ->and($audit->response_code)->toBe(200);
});

test('webhook with invalid HMAC signature is rejected with 401 and logged', function () {
    $univA = Institusi::factory()->create([
        'kode' => 'UNIV-A',
        'webhook_secret' => 'real_secret_key',
    ]);

    $payload = [
        'event' => 'payment.success',
        'institution_code' => 'UNIV-A',
        'bill_number' => 'TAG-001',
    ];

    $rawPayload = json_encode($payload);
    $invalidSignature = hash_hmac('sha256', $rawPayload, 'wrong_tampered_secret');

    $response = $this->withServerVariables([
        'HTTP_X_SIGNATURE' => $invalidSignature,
        'CONTENT_TYPE' => 'application/json',
    ])->call('POST', '/api/v1/webhook/payment', [], [], [], [], $rawPayload);

    $response->assertStatus(401);
    $response->assertJsonPath('status', 'error');

    // Audit log must record the failed attempt
    $audit = WebhookAuditLog::latest()->first();
    expect($audit->is_signature_valid)->toBeFalse()
        ->and($audit->response_code)->toBe(401);
});

test('webhook is idempotent when duplicate success notifications arrive', function () {
    $secret = 'idempotent_secret';
    $univA = Institusi::factory()->create([
        'kode' => 'UNIV-A',
        'webhook_secret' => $secret,
    ]);

    $student = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    $tagihan = Tagihan::factory()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'nomor_tagihan' => 'TAG-IDEM-001',
        'nominal' => 3000000.00,
        'status' => Tagihan::STATUS_BELUM_BAYAR,
    ]);

    $payload = [
        'event' => 'payment.success',
        'institution_code' => 'UNIV-A',
        'bill_number' => 'TAG-IDEM-001',
        'amount' => 3000000.00,
        'transaction_reference' => 'TRX-DUPLICATE-001',
        'payment_time' => now()->toIso8601String(),
    ];

    $rawPayload = json_encode($payload);
    $signature = hash_hmac('sha256', $rawPayload, $secret);

    // Call 1: Should succeed
    $response1 = $this->withServerVariables([
        'HTTP_X_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ])->call('POST', '/api/v1/webhook/payment', [], [], [], [], $rawPayload);
    $response1->assertStatus(200);

    // Call 2: Duplicate request - should return 200 without double recording
    $response2 = $this->withServerVariables([
        'HTTP_X_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ])->call('POST', '/api/v1/webhook/payment', [], [], [], [], $rawPayload);
    $response2->assertStatus(200);
    $response2->assertJsonPath('status', 'ignored');

    // Transaction ledger must only contain 1 record
    expect(RiwayatTransaksi::where('tagihan_id', $tagihan->id)->count())->toBe(1);
});

test('event payment.expired does not override an already paid bill', function () {
    $secret = 'secret_out_of_order';
    $univA = Institusi::factory()->create([
        'kode' => 'UNIV-A',
        'webhook_secret' => $secret,
    ]);

    $student = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    // Bill already paid
    $tagihan = Tagihan::factory()->lunas()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'nomor_tagihan' => 'TAG-ALREADY-PAID',
    ]);

    $payload = [
        'event' => 'payment.expired',
        'institution_code' => 'UNIV-A',
        'bill_number' => 'TAG-ALREADY-PAID',
    ];

    $rawPayload = json_encode($payload);
    $signature = hash_hmac('sha256', $rawPayload, $secret);

    $response = $this->withServerVariables([
        'HTTP_X_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ])->call('POST', '/api/v1/webhook/payment', [], [], [], [], $rawPayload);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 'ignored');

    // Tagihan must remain LUNAS
    $tagihan->refresh();
    expect($tagihan->status)->toBe(Tagihan::STATUS_LUNAS);
});

test('webhook accepts indonesian alias field names', function () {
    $secret = 'secret_indonesia_bilingual';
    $univA = Institusi::factory()->create([
        'kode' => 'UNIV-INA',
        'webhook_secret' => $secret,
    ]);

    $student = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    $tagihan = Tagihan::factory()->belumBayar()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'nomor_tagihan' => 'TAG-INA-001',
        'nominal' => 3500000.00,
    ]);

    // Payload in Bahasa Indonesia fields
    $payload = [
        'event' => 'payment.success',
        'kode_institusi' => 'UNIV-INA',
        'nomor_tagihan' => 'TAG-INA-001',
        'nominal' => 3500000.00,
        'referensi_gateway' => 'TRX-INA-9988',
        'waktu_transaksi' => now()->toIso8601String(),
    ];

    $rawPayload = json_encode($payload);
    $signature = hash_hmac('sha256', $rawPayload, $secret);

    $response = $this->withServerVariables([
        'HTTP_X_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ])->call('POST', '/api/v1/webhook/payment', [], [], [], [], $rawPayload);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 'success');

    $tagihan->refresh();
    expect($tagihan->status)->toBe(Tagihan::STATUS_LUNAS);
    expect(RiwayatTransaksi::where('referensi_gateway', 'TRX-INA-9988')->exists())->toBeTrue();
});
