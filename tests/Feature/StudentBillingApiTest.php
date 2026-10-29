<?php

use App\Models\Institusi;
use App\Models\RiwayatTransaksi;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student billing endpoint returns bills and transaction history', function () {
    $univA = Institusi::factory()->create(['kode' => 'UNIV-A']);
    $student = User::factory()->create([
        'institusi_id' => $univA->id,
        'role' => 'mahasiswa',
        'name' => 'Budi Santoso',
        'nim' => '2024010001',
    ]);

    $tagihan = Tagihan::factory()->lunas()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'nomor_tagihan' => 'TAG-API-001',
        'nominal' => 4500000.00,
    ]);

    RiwayatTransaksi::create([
        'tagihan_id' => $tagihan->id,
        'institusi_id' => $univA->id,
        'event_type' => 'payment.success',
        'nominal_bayar' => 4500000.00,
        'referensi_gateway' => 'GATEWAY-API-99',
        'waktu_transaksi' => now(),
        'payload_snapshot' => ['ref' => 'GATEWAY-API-99'],
    ]);

    $response = $this->getJson("/api/v1/students/{$student->id}/bills");

    $response->assertStatus(200);
    $response->assertJsonPath('status', 'success');
    $response->assertJsonPath('data.mahasiswa.nama', 'Budi Santoso');
    $response->assertJsonPath('data.krs_status.is_eligible', true);
    $response->assertJsonPath('data.tagihan_list.0.nomor_tagihan', 'TAG-API-001');
    $response->assertJsonPath('data.tagihan_list.0.riwayat_transaksi.0.referensi_gateway', 'GATEWAY-API-99');
});

