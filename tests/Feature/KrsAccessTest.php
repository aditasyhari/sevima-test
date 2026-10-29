<?php

use App\Models\Institusi;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mahasiswa with lunas ukt can access krs portal with eligible status', function () {
    $univA = Institusi::factory()->create(['kode' => 'UNIV-A']);
    $student = User::factory()->create([
        'institusi_id' => $univA->id,
        'role' => 'mahasiswa',
        'name' => 'Budi Santoso',
        'nim' => '2024010001',
    ]);

    Tagihan::factory()->lunas()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'semester' => '2024/2025 Ganjil',
    ]);

    $response = $this->actingAs($student)->get('/mahasiswa/krs');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Mahasiswa/Krs')
        ->where('krs_status.is_eligible', true)
        ->has('courses')
    );
});

test('mahasiswa with belum_bayar ukt is locked from krs portal', function () {
    $univA = Institusi::factory()->create(['kode' => 'UNIV-A']);
    $student = User::factory()->create([
        'institusi_id' => $univA->id,
        'role' => 'mahasiswa',
        'name' => 'Andi Pratama',
        'nim' => '2024020001',
    ]);

    Tagihan::factory()->belumBayar()->create([
        'institusi_id' => $univA->id,
        'mahasiswa_id' => $student->id,
        'semester' => '2024/2025 Ganjil',
    ]);

    $response = $this->actingAs($student)->get('/mahasiswa/krs');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Mahasiswa/Krs')
        ->where('krs_status.is_eligible', false)
    );
});

test('unauthenticated users cannot access krs portal', function () {
    $response = $this->get('/mahasiswa/krs');
    $response->assertRedirect('/login');
});

