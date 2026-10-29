<?php

use App\Models\Institusi;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin cannot see bills belonging to another institution', function () {
    $univA = Institusi::factory()->create(['kode' => 'UNIV-A']);
    $univB = Institusi::factory()->create(['kode' => 'UNIV-B']);

    $adminA = User::factory()->create([
        'institusi_id' => $univA->id,
        'role' => 'admin_keuangan',
    ]);

    $studentA = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    $studentB = User::factory()->create(['institusi_id' => $univB->id, 'role' => 'mahasiswa']);

    $tagihanA = Tagihan::factory()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $studentA->id,
        'nomor_tagihan' => 'TAG-A-001',
    ]);

    $tagihanB = Tagihan::factory()->create([
        'institusi_id' => $univB->id,
        'mahasiswa_id' => $studentB->id,
        'nomor_tagihan' => 'TAG-B-001',
    ]);

    $response = $this->actingAs($adminA)->get(route('admin.tagihan.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Tagihan/Index')
        ->has('tagihans.data', 1)
        ->where('tagihans.data.0.nomor_tagihan', 'TAG-A-001')
    );
});

test('bill number is unique per institution but can be identical across different institutions', function () {
    $univA = Institusi::factory()->create(['kode' => 'UNIV-A']);
    $univB = Institusi::factory()->create(['kode' => 'UNIV-B']);

    $studentA = User::factory()->create(['institusi_id' => $univA->id, 'role' => 'mahasiswa']);
    $studentB = User::factory()->create(['institusi_id' => $univB->id, 'role' => 'mahasiswa']);

    // Create same bill number 'TAG-IDENTIK-01' in Univ A
    $tagihanA = Tagihan::factory()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $studentA->id,
        'nomor_tagihan' => 'TAG-IDENTIK-01',
    ]);

    // Create same bill number 'TAG-IDENTIK-01' in Univ B - MUST SUCCEED
    $tagihanB = Tagihan::factory()->create([
        'institusi_id' => $univB->id,
        'mahasiswa_id' => $studentB->id,
        'nomor_tagihan' => 'TAG-IDENTIK-01',
    ]);

    expect($tagihanA->nomor_tagihan)->toBe('TAG-IDENTIK-01')
        ->and($tagihanB->nomor_tagihan)->toBe('TAG-IDENTIK-01')
        ->and($tagihanA->institusi_id)->not->toBe($tagihanB->institusi_id);
});

test('admin cannot create a bill for a student from another institution', function () {
    $univA = Institusi::factory()->create(['kode' => 'UNIV-A']);
    $univB = Institusi::factory()->create(['kode' => 'UNIV-B']);

    $adminA = User::factory()->create([
        'institusi_id' => $univA->id,
        'role' => 'admin_keuangan',
    ]);

    // Student belongs to Univ B
    $studentB = User::factory()->create(['institusi_id' => $univB->id, 'role' => 'mahasiswa']);

    $response = $this->actingAs($adminA)->post(route('admin.tagihan.store'), [
        'mahasiswa_id' => $studentB->id,
        'nomor_tagihan' => 'TAG-ATTEMPT-01',
        'semester' => '2024/2025 Ganjil',
        'nominal' => 4500000,
        'tanggal_kedaluwarsa' => now()->addDays(7)->toDateTimeString(),
    ]);

    $response->assertSessionHasErrors('mahasiswa_id');
    expect(Tagihan::where('nomor_tagihan', 'TAG-ATTEMPT-01')->exists())->toBeFalse();
});
