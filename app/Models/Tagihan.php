<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use App\Models\Concerns\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    use HasFactory, HasUuidV7, BelongsToInstitution;

    public const STATUS_BELUM_BAYAR = 'belum_bayar';
    public const STATUS_LUNAS = 'lunas';
    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    protected $fillable = [
        'institusi_id',
        'mahasiswa_id',
        'nomor_tagihan',
        'semester',
        'nominal',
        'tanggal_kedaluwarsa',
        'status',
        'virtual_account',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal_kedaluwarsa' => 'datetime',
        ];
    }

    /**
     * Relationship to the Student.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    /**
     * Relationship to Transaction Ledger.
     */
    public function riwayatTransaksis(): HasMany
    {
        return $this->hasMany(RiwayatTransaksi::class, 'tagihan_id');
    }

    public function isLunas(): bool
    {
        return $this->status === self::STATUS_LUNAS;
    }

    public function isBelumBayar(): bool
    {
        return $this->status === self::STATUS_BELUM_BAYAR;
    }

    public function isKedaluwarsa(): bool
    {
        return $this->status === self::STATUS_KEDALUWARSA;
    }
}
