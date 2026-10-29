<?php

namespace Database\Seeders;

use App\Models\Institusi;
use App\Models\RiwayatTransaksi;
use App\Models\Tagihan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with 3 institutions and demo accounts.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // ==========================================
        // 1. INSTITUSI A: Universitas Negeri Gadjah Sakti
        // ==========================================
        $univA = Institusi::create([
            'kode' => 'UNIV-A',
            'nama' => 'Universitas Negeri Gadjah Sakti',
            'webhook_secret' => 'secret_univ_a_98fbc12a',
        ]);

        $adminA = User::create([
            'institusi_id' => $univA->id,
            'role' => 'admin_keuangan',
            'name' => 'Admin Keuangan Gadjah Sakti',
            'email' => 'admin.univa@sevima.test',
            'password' => $password,
        ]);

        $mhsA1 = User::create([
            'institusi_id' => $univA->id,
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
            'email' => 'budi.univa@sevima.test',
            'password' => $password,
            'nim' => '2024010001',
            'prodi' => 'Teknik Informatika',
            'semester_aktif' => 5,
        ]);

        $mhsA2 = User::create([
            'institusi_id' => $univA->id,
            'role' => 'mahasiswa',
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.univa@sevima.test',
            'password' => $password,
            'nim' => '2024010002',
            'prodi' => 'Sistem Informasi',
            'semester_aktif' => 3,
        ]);

        // Tagihan Mahasiswa A1 (Budi) - Semester 5 (Belum Bayar)
        Tagihan::create([
            'institusi_id' => $univA->id,
            'mahasiswa_id' => $mhsA1->id,
            'nomor_tagihan' => 'TAG-2024-001',
            'semester' => '2024/2025 Ganjil',
            'nominal' => 4500000.00,
            'tanggal_kedaluwarsa' => Carbon::now()->addDays(14),
            'status' => Tagihan::STATUS_BELUM_BAYAR,
            'virtual_account' => '8801'.$mhsA1->nim,
            'catatan' => 'UKT Semester 5 Reguler',
        ]);

        // Tagihan Mahasiswa A1 (Budi) - Semester 4 (Lunas)
        $tagihanLunasA = Tagihan::create([
            'institusi_id' => $univA->id,
            'mahasiswa_id' => $mhsA1->id,
            'nomor_tagihan' => 'TAG-2023-004',
            'semester' => '2023/2024 Genap',
            'nominal' => 4500000.00,
            'tanggal_kedaluwarsa' => Carbon::now()->subMonths(6),
            'status' => Tagihan::STATUS_LUNAS,
            'virtual_account' => '8801'.$mhsA1->nim,
            'catatan' => 'UKT Semester 4 Reguler',
        ]);

        RiwayatTransaksi::create([
            'tagihan_id' => $tagihanLunasA->id,
            'institusi_id' => $univA->id,
            'event_type' => 'payment.success',
            'nominal_bayar' => 4500000.00,
            'referensi_gateway' => 'MOCK-GW-A-99812',
            'waktu_transaksi' => Carbon::now()->subMonths(6)->addDays(2),
            'payload_snapshot' => [
                'event' => 'payment.success',
                'amount' => 4500000.00,
                'reference' => 'MOCK-GW-A-99812',
            ],
        ]);

        // Tagihan Mahasiswa A2 (Siti) - Semester 3 (Lunas)
        $tagihanLunasSiti = Tagihan::create([
            'institusi_id' => $univA->id,
            'mahasiswa_id' => $mhsA2->id,
            'nomor_tagihan' => 'TAG-2024-002',
            'semester' => '2024/2025 Ganjil',
            'nominal' => 3800000.00,
            'tanggal_kedaluwarsa' => Carbon::now()->addDays(10),
            'status' => Tagihan::STATUS_LUNAS,
            'virtual_account' => '8801'.$mhsA2->nim,
            'catatan' => 'UKT Semester 3 Reguler',
        ]);

        RiwayatTransaksi::create([
            'tagihan_id' => $tagihanLunasSiti->id,
            'institusi_id' => $univA->id,
            'event_type' => 'payment.success',
            'nominal_bayar' => 3800000.00,
            'referensi_gateway' => 'MOCK-GW-A-10294',
            'waktu_transaksi' => Carbon::now()->subDays(1),
            'payload_snapshot' => [
                'event' => 'payment.success',
                'amount' => 3800000.00,
                'reference' => 'MOCK-GW-A-10294',
            ],
        ]);

        // ==========================================
        // 2. INSTITUSI B: Institut Teknologi Nusantara
        // ==========================================
        $univB = Institusi::create([
            'kode' => 'UNIV-B',
            'nama' => 'Institut Teknologi Nusantara',
            'webhook_secret' => 'secret_univ_b_77ea410d',
        ]);

        $adminB = User::create([
            'institusi_id' => $univB->id,
            'role' => 'admin_keuangan',
            'name' => 'Admin Keuangan ITN',
            'email' => 'admin.univb@sevima.test',
            'password' => $password,
        ]);

        $mhsB1 = User::create([
            'institusi_id' => $univB->id,
            'role' => 'mahasiswa',
            'name' => 'Andi Pratama',
            'email' => 'andi.univb@sevima.test',
            'password' => $password,
            'nim' => '2024020001',
            'prodi' => 'Teknik Elektro',
            'semester_aktif' => 5,
        ]);

        // Membuktikan nomor tagihan bisa sama antar institusi (TAG-2024-001)
        Tagihan::create([
            'institusi_id' => $univB->id,
            'mahasiswa_id' => $mhsB1->id,
            'nomor_tagihan' => 'TAG-2024-001', // Sama dengan UNIV-A
            'semester' => '2024/2025 Ganjil',
            'nominal' => 5200000.00,
            'tanggal_kedaluwarsa' => Carbon::now()->addDays(20),
            'status' => Tagihan::STATUS_BELUM_BAYAR,
            'virtual_account' => '8802'.$mhsB1->nim,
            'catatan' => 'UKT Semester 5 ITN',
        ]);

        // ==========================================
        // 3. INSTITUSI C: Politeknik Harapan Bangsa
        // ==========================================
        $univC = Institusi::create([
            'kode' => 'UNIV-C',
            'nama' => 'Politeknik Harapan Bangsa',
            'webhook_secret' => 'secret_univ_c_13cd99fa',
        ]);

        $adminC = User::create([
            'institusi_id' => $univC->id,
            'role' => 'admin_keuangan',
            'name' => 'Admin Keuangan Poltek',
            'email' => 'admin.univc@sevima.test',
            'password' => $password,
        ]);

        $mhsC1 = User::create([
            'institusi_id' => $univC->id,
            'role' => 'mahasiswa',
            'name' => 'Maya Anggraini',
            'email' => 'maya.univc@sevima.test',
            'password' => $password,
            'nim' => '2024030001',
            'prodi' => 'Akuntansi Keuangan',
            'semester_aktif' => 4,
        ]);

        // Tagihan Kedaluwarsa (Expired)
        Tagihan::create([
            'institusi_id' => $univC->id,
            'mahasiswa_id' => $mhsC1->id,
            'nomor_tagihan' => 'TAG-2024-EXPIRED',
            'semester' => '2024/2025 Ganjil',
            'nominal' => 3200000.00,
            'tanggal_kedaluwarsa' => Carbon::now()->subDays(5),
            'status' => Tagihan::STATUS_KEDALUWARSA,
            'virtual_account' => '8803'.$mhsC1->nim,
            'catatan' => 'UKT Semester 4 Poltek (Expired)',
        ]);
    }
}
