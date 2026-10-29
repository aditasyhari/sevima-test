<?php

namespace App\Actions\Webhook;

use App\Models\Institusi;
use App\Models\RiwayatTransaksi;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessPaymentWebhookAction
{
    /**
     * Process payment webhook event with pessimistic lock and idempotency safeguards.
     *
     * @param Institusi $institusi
     * @param array $payload
     * @return array [
     *     'status' => 'success' | 'ignored',
     *     'message' => string,
     *     'tagihan' => Tagihan,
     *     'http_code' => int
     * ]
     * @throws \Exception
     */
    public function execute(Institusi $institusi, array $payload): array
    {
        $event = $payload['event'] ?? null;
        $billNumber = $payload['bill_number'] ?? null;
        $amount = isset($payload['amount']) ? (float) $payload['amount'] : 0.0;
        $reference = $payload['transaction_reference'] ?? ('REF-' . uniqid());
        $paymentTime = isset($payload['payment_time']) ? Carbon::parse($payload['payment_time']) : Carbon::now();

        if (empty($event) || empty($billNumber)) {
            throw ValidationException::withMessages([
                'payload' => 'Field event dan bill_number wajib diisi.',
            ]);
        }

        return DB::transaction(function () use ($institusi, $event, $billNumber, $amount, $reference, $paymentTime, $payload) {
            // Find bill scoped by institution with pessimistic locking to prevent concurrency race conditions
            $tagihan = Tagihan::withoutGlobalScope('institution')
                ->where('institusi_id', $institusi->id)
                ->where('nomor_tagihan', $billNumber)
                ->lockForUpdate()
                ->first();

            if (!$tagihan) {
                return [
                    'status' => 'error',
                    'message' => "Tagihan dengan nomor {$billNumber} tidak ditemukan pada institusi {$institusi->kode}.",
                    'tagihan' => null,
                    'http_code' => 404,
                ];
            }

            // ========================================================
            // Skenario 1: Event = payment.success
            // ========================================================
            if ($event === 'payment.success') {
                // Idempotency check: jika tagihan sudah lunas
                if ($tagihan->isLunas()) {
                    // Cek apakah referensi transaksi ini sudah tercatat sebelumnya
                    $existingTrx = RiwayatTransaksi::withoutGlobalScope('institution')
                        ->where('institusi_id', $institusi->id)
                        ->where('referensi_gateway', $reference)
                        ->first();

                    if ($existingTrx) {
                        return [
                            'status' => 'ignored',
                            'message' => "Idempotent: Pembayaran dengan referensi {$reference} sudah pernah diproses sebelumnya.",
                            'tagihan' => $tagihan,
                            'http_code' => 200,
                        ];
                    }

                    return [
                        'status' => 'ignored',
                        'message' => "Idempotent: Tagihan {$billNumber} sudah berstatus lunas.",
                        'tagihan' => $tagihan,
                        'http_code' => 200,
                    ];
                }

                // Update status tagihan menjadi lunas
                $previousStatus = $tagihan->status;
                $tagihan->status = Tagihan::STATUS_LUNAS;
                $tagihan->save();

                // Catat riwayat transaksi (ledger)
                $riwayat = RiwayatTransaksi::create([
                    'tagihan_id' => $tagihan->id,
                    'institusi_id' => $institusi->id,
                    'event_type' => 'payment.success',
                    'nominal_bayar' => $amount > 0 ? $amount : $tagihan->nominal,
                    'referensi_gateway' => $reference,
                    'waktu_transaksi' => $paymentTime,
                    'payload_snapshot' => $payload,
                ]);

                $note = $previousStatus === Tagihan::STATUS_KEDALUWARSA
                    ? "Pembayaran berhasil diterima untuk tagihan yang sebelumnya kedaluwarsa. Status diubah menjadi Lunas."
                    : "Pembayaran berhasil diverifikasi. Status tagihan diubah menjadi Lunas.";

                return [
                    'status' => 'success',
                    'message' => $note,
                    'tagihan' => $tagihan,
                    'http_code' => 200,
                ];
            }

            // ========================================================
            // Skenario 2: Event = payment.expired
            // ========================================================
            if ($event === 'payment.expired') {
                // Aturan kritis: Tagihan yang SUDAH LUNAS tidak boleh di-expire oleh webhook out-of-order
                if ($tagihan->isLunas()) {
                    return [
                        'status' => 'ignored',
                        'message' => "Event payment.expired diabaikan karena tagihan sudah lunas.",
                        'tagihan' => $tagihan,
                        'http_code' => 200,
                    ];
                }

                if ($tagihan->isKedaluwarsa()) {
                    return [
                        'status' => 'ignored',
                        'message' => "Idempotent: Tagihan {$billNumber} sudah berstatus kedaluwarsa.",
                        'tagihan' => $tagihan,
                        'http_code' => 200,
                    ];
                }

                // Update status menjadi kedaluwarsa
                $tagihan->status = Tagihan::STATUS_KEDALUWARSA;
                $tagihan->save();

                RiwayatTransaksi::create([
                    'tagihan_id' => $tagihan->id,
                    'institusi_id' => $institusi->id,
                    'event_type' => 'payment.expired',
                    'nominal_bayar' => 0.0,
                    'referensi_gateway' => $reference,
                    'waktu_transaksi' => $paymentTime,
                    'payload_snapshot' => $payload,
                ]);

                return [
                    'status' => 'success',
                    'message' => "Tagihan {$billNumber} telah diubah menjadi kedaluwarsa sesuai notifikasi gateway.",
                    'tagihan' => $tagihan,
                    'http_code' => 200,
                ];
            }

            return [
                'status' => 'error',
                'message' => "Event '{$event}' tidak dikenali. Event yang didukung: payment.success, payment.expired.",
                'tagihan' => $tagihan,
                'http_code' => 422,
            ];
        });
    }
}
