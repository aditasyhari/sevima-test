<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KrsController extends Controller
{
    /**
     * Display student KRS study plan page with gating access verification.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user()->load('institusi');

        // Check active semester bill payment state
        $activeBill = Tagihan::where('mahasiswa_id', $user->id)
            ->latest('created_at')
            ->first();

        $isEligible = $activeBill && $activeBill->isLunas();

        // Sample academic curriculum courses for active semester
        $availableCourses = [
            [
                'id' => 'MK-01',
                'kode' => 'IF3101',
                'nama' => 'Rekayasa Perangkat Lunak Terapan',
                'sks' => 3,
                'kelas' => 'A',
                'hari' => 'Senin',
                'jam' => '08:00 - 10:30',
                'ruang' => 'Lab Software 3',
                'dosen' => 'Dr. Ir. Wahyu Wibowo, M.T.',
                'selected' => true,
            ],
            [
                'id' => 'MK-02',
                'kode' => 'IF3102',
                'nama' => 'Basis Data Terdistribusi & Replikasi',
                'sks' => 3,
                'kelas' => 'B',
                'hari' => 'Senin',
                'jam' => '13:00 - 15:30',
                'ruang' => 'Ruang Teori 204',
                'dosen' => 'Prof. Retno Sari, M.Kom',
                'selected' => true,
            ],
            [
                'id' => 'MK-03',
                'kode' => 'IF3103',
                'nama' => 'Pemrograman Web Berbasis Komponen',
                'sks' => 3,
                'kelas' => 'A',
                'hari' => 'Selasa',
                'jam' => '09:00 - 11:30',
                'ruang' => 'Lab Komputer 1',
                'dosen' => 'Budi Raharjo, Ph.D',
                'selected' => true,
            ],
            [
                'id' => 'MK-04',
                'kode' => 'IF3104',
                'nama' => 'Keamanan Siber & Kriptografi Terapan',
                'sks' => 3,
                'kelas' => 'A',
                'hari' => 'Rabu',
                'jam' => '10:00 - 12:30',
                'ruang' => 'Ruang Teori 102',
                'dosen' => 'Ahmad Fauzi, M.T.',
                'selected' => true,
            ],
            [
                'id' => 'MK-05',
                'kode' => 'IF3105',
                'nama' => 'Arsitektur Cloud & DevOps Modern',
                'sks' => 3,
                'kelas' => 'C',
                'hari' => 'Kamis',
                'jam' => '08:00 - 10:30',
                'ruang' => 'Lab Jaringan',
                'dosen' => 'Hendra Kusuma, M.Kom',
                'selected' => true,
            ],
            [
                'id' => 'MK-06',
                'kode' => 'IF3106',
                'nama' => 'Kecerdasan Buatan & Pembelajaran Mesin',
                'sks' => 3,
                'kelas' => 'A',
                'hari' => 'Jumat',
                'jam' => '08:00 - 10:30',
                'ruang' => 'Ruang Teori 301',
                'dosen' => 'Dr. Nur Azizah, S.Kom',
                'selected' => true,
            ],
            [
                'id' => 'MK-07',
                'kode' => 'IF3107',
                'nama' => 'Etika Profesi & Metodologi Riset TI',
                'sks' => 2,
                'kelas' => 'A',
                'hari' => 'Jumat',
                'jam' => '13:30 - 15:10',
                'ruang' => 'Auditorium Gd. A',
                'dosen' => 'Prof. Bambang Sutrisno, M.Sc',
                'selected' => true,
            ],
        ];

        return Inertia::render('Mahasiswa/Krs', [
            'mahasiswa' => [
                'id' => $user->id,
                'name' => $user->name,
                'nim' => $user->nim,
                'prodi' => $user->prodi,
                'semester_aktif' => $user->semester_aktif,
                'dosen_pa' => 'Dr. Eng. Ir. Supriyanto, M.T. (NIP: 197805122003121002)',
                'institusi' => [
                    'nama' => $user->institusi?->nama,
                    'kode' => $user->institusi?->kode,
                ],
            ],
            'krs_status' => [
                'is_eligible' => $isEligible,
                'active_semester' => $activeBill?->semester ?? '2024/2025 Ganjil',
                'bill_status' => $activeBill?->status ?? 'belum_bayar',
                'bill_number' => $activeBill?->nomor_tagihan,
                'total_sks_max' => 24,
            ],
            'courses' => $availableCourses,
        ]);
    }
}
