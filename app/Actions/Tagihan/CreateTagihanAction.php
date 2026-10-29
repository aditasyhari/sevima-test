<?php

namespace App\Actions\Tagihan;

use App\Models\Tagihan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CreateTagihanAction
{
    /**
     * Create a new UKT bill for a student within the current institution.
     */
    public function execute(array $data, ?string $institusiId = null): Tagihan
    {
        $institusiId = $institusiId ?? auth()->user()?->institusi_id;

        if (! $institusiId) {
            throw ValidationException::withMessages([
                'institusi_id' => 'Institusi wajib ditentukan.',
            ]);
        }

        // Pastikan mahasiswa berada di bawah institusi yang sama
        $mahasiswa = User::withoutGlobalScope('institution')
            ->where('institusi_id', $institusiId)
            ->where('id', $data['mahasiswa_id'])
            ->where('role', 'mahasiswa')
            ->first();

        if (! $mahasiswa) {
            throw ValidationException::withMessages([
                'mahasiswa_id' => 'Mahasiswa tidak ditemukan atau bukan milik institusi ini.',
            ]);
        }

        // Cek duplikasi nomor tagihan di institusi yang sama
        $exists = Tagihan::withoutGlobalScope('institution')
            ->where('institusi_id', $institusiId)
            ->where('nomor_tagihan', $data['nomor_tagihan'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'nomor_tagihan' => "Nomor tagihan '{$data['nomor_tagihan']}' sudah digunakan pada institusi ini.",
            ]);
        }

        $virtualAccount = '88'.str_pad((string) mt_rand(10, 99), 2, '0', STR_PAD_LEFT).($mahasiswa->nim ?? '000000');

        return Tagihan::create([
            'institusi_id' => $institusiId,
            'mahasiswa_id' => $mahasiswa->id,
            'nomor_tagihan' => $data['nomor_tagihan'],
            'semester' => $data['semester'],
            'nominal' => $data['nominal'],
            'tanggal_kedaluwarsa' => Carbon::parse($data['tanggal_kedaluwarsa']),
            'status' => Tagihan::STATUS_BELUM_BAYAR,
            'virtual_account' => $data['virtual_account'] ?? $virtualAccount,
            'catatan' => $data['catatan'] ?? null,
        ]);
    }
}
