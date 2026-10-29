<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display student dashboard with active bill, KRS status, and transaction ledger.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user()->load('institusi');

        // Scoped automatically to current student
        $bills = Tagihan::with(['riwayatTransaksis' => function ($q) {
            $q->latest('waktu_transaksi');
        }])
            ->where('mahasiswa_id', $user->id)
            ->latest('created_at')
            ->get();

        $activeBill = $bills->first();
        $isKrsEligible = $activeBill && $activeBill->isLunas();

        return Inertia::render('Mahasiswa/Dashboard', [
            'mahasiswa' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nim' => $user->nim,
                'prodi' => $user->prodi,
                'semester_aktif' => $user->semester_aktif,
                'institusi' => [
                    'id' => $user->institusi?->id,
                    'kode' => $user->institusi?->kode,
                    'nama' => $user->institusi?->nama,
                ],
            ],
            'krs_status' => [
                'is_eligible' => $isKrsEligible,
                'label' => $isKrsEligible ? 'SIAP ISI KRS' : 'KRS TERKUNCI',
                'description' => $isKrsEligible
                    ? 'Pembayaran UKT telah diverifikasi lunas oleh Payment Gateway. Anda diizinkan mengisi Kartu Rencana Studi (KRS).'
                    : 'Akses pengisian KRS terkunci. Silakan selesaikan pembayaran tagihan UKT aktif Anda.',
            ],
            'active_bill' => $activeBill ? [
                'id' => $activeBill->id,
                'nomor_tagihan' => $activeBill->nomor_tagihan,
                'semester' => $activeBill->semester,
                'nominal' => (float) $activeBill->nominal,
                'tanggal_kedaluwarsa' => $activeBill->tanggal_kedaluwarsa->format('d M Y, H:i'),
                'is_expired' => $activeBill->tanggal_kedaluwarsa->isPast(),
                'status' => $activeBill->status,
                'virtual_account' => $activeBill->virtual_account,
                'catatan' => $activeBill->catatan,
                'riwayat_transaksi' => $activeBill->riwayatTransaksis,
            ] : null,
            'past_bills' => $bills->slice(1)->values()->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'nomor_tagihan' => $bill->nomor_tagihan,
                    'semester' => $bill->semester,
                    'nominal' => (float) $bill->nominal,
                    'tanggal_kedaluwarsa' => $bill->tanggal_kedaluwarsa->format('d M Y'),
                    'status' => $bill->status,
                    'riwayat_transaksi' => $bill->riwayatTransaksis,
                ];
            }),
        ]);
    }
}
