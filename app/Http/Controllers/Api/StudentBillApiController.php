<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentBillApiController extends Controller
{
    /**
     * Get billing status and transaction history for a specific student (by UUID or NIM).
     */
    public function show(Request $request, string $studentId): JsonResponse
    {
        $student = User::withoutGlobalScope('institution')
            ->with('institusi')
            ->where('role', 'mahasiswa')
            ->where(function ($query) use ($studentId) {
                $query->where('id', $studentId)
                    ->orWhere('nim', $studentId);
            })
            ->first();

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => "Mahasiswa dengan identifier '{$studentId}' (ID atau NIM) tidak ditemukan.",
            ], 404);
        }

        // Get bills for this student with optional filters
        $billsQuery = Tagihan::withoutGlobalScope('institution')
            ->with(['riwayatTransaksis' => function ($q) {
                $q->latest('waktu_transaksi');
            }])
            ->where('mahasiswa_id', $student->id);

        if ($request->filled('status')) {
            $billsQuery->where('status', $request->query('status'));
        }

        if ($request->filled('semester')) {
            $billsQuery->where('semester', $request->query('semester'));
        }

        $bills = $billsQuery->orderByDesc('created_at')->get();

        // Check KRS Clearance (Lunas on latest semester bill)
        $activeBill = $bills->first();
        $isKrsEligible = $activeBill && $activeBill->isLunas();

        return response()->json([
            'status' => 'success',
            'data' => [
                'mahasiswa' => [
                    'id' => $student->id,
                    'nama' => $student->name,
                    'nim' => $student->nim,
                    'prodi' => $student->prodi,
                    'semester_aktif' => $student->semester_aktif,
                    'institusi' => [
                        'id' => $student->institusi?->id,
                        'kode' => $student->institusi?->kode,
                        'nama' => $student->institusi?->nama,
                    ],
                ],
                'krs_status' => [
                    'is_eligible' => $isKrsEligible,
                    'status_label' => $isKrsEligible ? 'SIAP ISI KRS' : 'KRS TERKUNCI',
                    'keterangan' => $isKrsEligible
                        ? 'Tagihan UKT semester aktif telah lunas diverifikasi.'
                        : 'Selesaikan pembayaran UKT semester ini untuk membuka akses pengisian KRS.',
                ],
                'tagihan_list' => $bills->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'nomor_tagihan' => $bill->nomor_tagihan,
                        'semester' => $bill->semester,
                        'nominal' => (float) $bill->nominal,
                        'status' => $bill->status,
                        'status_label' => strtoupper(str_replace('_', ' ', $bill->status)),
                        'tanggal_kedaluwarsa' => $bill->tanggal_kedaluwarsa->toIso8601String(),
                        'virtual_account' => $bill->virtual_account,
                        'catatan' => $bill->catatan,
                        'riwayat_transaksi' => $bill->riwayatTransaksis->map(function ($trx) {
                            return [
                                'id' => $trx->id,
                                'event_type' => $trx->event_type,
                                'nominal_bayar' => (float) $trx->nominal_bayar,
                                'referensi_gateway' => $trx->referensi_gateway,
                                'waktu_transaksi' => $trx->waktu_transaksi->toIso8601String(),
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }
}
